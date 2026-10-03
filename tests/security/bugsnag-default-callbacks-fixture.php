<?php

namespace verbb\bugsnag {
    final class Bugsnag
    {
        public static mixed $plugin = null;
    }
}

namespace verbb\bugsnag\models {
    final class Settings
    {
        public string $appVersion = '5.4.3';
        public array $notifyReleaseStages = ['production'];
        public array $filters = ['password'];
        public array $metaData = ['configured' => ['retained' => true]];

        public function getEnabled(): bool
        {
            return true;
        }

        public function getServerApiKey(): string
        {
            return '0123456789abcdef0123456789abcdef';
        }

        public function getReleaseStage(): string
        {
            return 'production';
        }

        public function getUser(): array
        {
            return ['id' => 'configured-user'];
        }

        public function shouldIgnoreCurrentRequest(): bool
        {
            return false;
        }

        public function getCommerceAutoBreadcrumbs(): bool
        {
            return false;
        }

        public function getCommerceAutoMetadata(): bool
        {
            return false;
        }
    }
}

namespace verbb\bugsnag\helpers {
    final class Bot
    {
        public static function isCurrentRequestCrawler(): bool
        {
            return false;
        }
    }

    final class User
    {
        public static function resolve(mixed $user): array
        {
            return is_array($user) ? $user : [];
        }
    }
}

namespace {
    use Bugsnag\Breadcrumbs\Breadcrumb;
    use Bugsnag\Client;
    use Bugsnag\Report;

    use verbb\bugsnag\Bugsnag;
    use verbb\bugsnag\log\BugsnagTarget;
    use verbb\bugsnag\models\Settings;
    use verbb\bugsnag\services\Service;

    use yii\log\Logger;

    $vendorPath = getenv('BUGSNAG_CRAFT_VENDOR') ?: dirname(__DIR__, 2) . '/vendor';

    if (!is_file($vendorPath . '/autoload.php')) {
        throw new RuntimeException('Set BUGSNAG_CRAFT_VENDOR to a Craft 5 vendor directory with the Bugsnag PHP SDK.');
    }

    require $vendorPath . '/autoload.php';
    require $vendorPath . '/yiisoft/yii2/Yii.php';
    require $vendorPath . '/craftcms/cms/src/Craft.php';
    require dirname(__DIR__, 2) . '/src/services/Service.php';
    require dirname(__DIR__, 2) . '/src/log/BugsnagTarget.php';

    final class BugsnagDefaultCallbacksFixturePlugin
    {
        public ?Service $service = null;

        public function __construct(private readonly Settings $settings)
        {
        }

        public function getSettings(): Settings
        {
            return $this->settings;
        }

        public function getService(): ?Service
        {
            return $this->service;
        }
    }

    function fixtureAssert(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }

    function captureReport(Client $client, string $name, string $message): Report
    {
        $captured = null;
        $report = Report::fromNamedError($client->getConfig(), $name, $message);
        $client->getPipeline()->execute($report, function(Report $report) use (&$captured): void {
            $captured = $report;
        });

        fixtureAssert($captured instanceof Report, 'The Bugsnag pipeline must preserve an eligible report.');

        return $captured;
    }

    function assertSecretsAbsent(array $report, array $secrets, string $path): void
    {
        $serialized = json_encode($report, JSON_THROW_ON_ERROR);

        foreach ($secrets as $secret) {
            fixtureAssert(!str_contains($serialized, $secret), "$path must not contain $secret.");
        }
    }

    $secrets = [
        'fixture-query-credential',
        'fixture-body-secret',
        'fixture-body-csrf',
        'fixture-authorization-secret',
        'fixture-header-csrf',
        'fixture-session-auth',
        'fixture-session-csrf',
        'fixture-cookie-secret',
        'fixture-cookie-csrf',
    ];

    $_SERVER = [
        'REQUEST_METHOD' => 'POST',
        'REQUEST_URI' => '/checkout?credential=' . $secrets[0],
        'HTTP_HOST' => 'fixture.test',
        'HTTPS' => 'on',
        'SERVER_PORT' => 443,
        'REMOTE_ADDR' => '192.0.2.10',
        'HTTP_AUTHORIZATION' => 'Bearer ' . $secrets[3],
        'HTTP_X_CSRF_TOKEN' => $secrets[4],
        'HTTP_COOKIE' => 'CraftSessionId=' . $secrets[7],
    ];
    $_GET = ['credential' => $secrets[0]];
    $_POST = [
        'ordinaryField' => $secrets[1],
        'CRAFT_CSRF_TOKEN' => $secrets[2],
    ];
    $_SESSION = [
        'authKey' => $secrets[5],
        'csrfToken' => $secrets[6],
    ];
    $_COOKIE = [
        'CraftSessionId' => $secrets[7],
        'CRAFT_CSRF_TOKEN' => $secrets[8],
    ];

    $settings = new Settings();
    $plugin = new BugsnagDefaultCallbacksFixturePlugin($settings);
    Bugsnag::$plugin = $plugin;

    $service = new Service();
    $plugin->service = $service;
    $service->metadata(['runtime' => ['retained' => true]]);
    $service->breadcrumb('Explicit fixture breadcrumb', Breadcrumb::MANUAL_TYPE, ['retained' => true]);
    $serviceClient = $service->getClient();
    fixtureAssert($serviceClient instanceof Client, 'The enabled service must construct a Bugsnag client.');

    $serviceReport = captureReport($serviceClient, 'FixtureServiceError', 'Explicit service error.')->toArray();
    assertSecretsAbsent($serviceReport, $secrets, 'The service report');
    fixtureAssert($serviceReport['exceptions'][0]['errorClass'] === 'FixtureServiceError', 'The service report must retain the explicit error class.');
    fixtureAssert($serviceReport['exceptions'][0]['message'] === 'Explicit service error.', 'The service report must retain the explicit error message.');
    fixtureAssert($serviceReport['app']['releaseStage'] === 'production', 'The service report must retain the configured release stage.');
    fixtureAssert($serviceReport['app']['version'] === '5.4.3', 'The service report must retain the configured app version.');
    fixtureAssert($serviceReport['user']['id'] === 'configured-user', 'The service report must retain the configured user.');
    fixtureAssert($serviceReport['metaData']['configured']['retained'] === true, 'The service report must retain configured metadata.');
    fixtureAssert($serviceReport['metaData']['runtime']['retained'] === true, 'The service report must retain runtime metadata.');
    fixtureAssert($serviceReport['breadcrumbs'][0]['name'] === 'Explicit fixture breadcrumb', 'The service report must retain explicit breadcrumbs.');

    $target = new BugsnagTarget([
        'serverApiKey' => '0123456789abcdef0123456789abcdef',
        'releaseStage' => 'production',
        'appVersion' => '5.4.3',
        'notifyReleaseStages' => ['production'],
        'filters' => ['password'],
        'metaData' => ['configured' => ['retained' => true]],
        'user' => ['id' => 'configured-target-user'],
    ]);
    $clientProperty = new ReflectionProperty(BugsnagTarget::class, '_bugsnag');
    $targetClient = $clientProperty->getValue($target);
    fixtureAssert($targetClient instanceof Client, 'The standalone target must construct a fallback Bugsnag client.');

    $targetReport = captureReport($targetClient, 'FixtureTargetError', 'Explicit target error.');
    $timestamp = 1234567890.25;
    $traces = [['file' => '/fixture/source.php', 'line' => 42]];
    $customizeReport = new ReflectionMethod(BugsnagTarget::class, '_customizeReport');
    $customizeReport->invoke($target, $targetReport, [
        'Explicit target error.',
        Logger::LEVEL_ERROR,
        'fixture.category',
        $timestamp,
        $traces,
        1024,
    ], 'fixture.category', 'error', 'error');

    $targetReport = $targetReport->toArray();
    assertSecretsAbsent($targetReport, $secrets, 'The standalone target report');
    fixtureAssert($targetReport['exceptions'][0]['errorClass'] === 'FixtureTargetError', 'The target report must retain the explicit error class.');
    fixtureAssert($targetReport['exceptions'][0]['message'] === 'Explicit target error.', 'The target report must retain the explicit error message.');
    fixtureAssert($targetReport['severity'] === 'error', 'The target report must retain the mapped severity.');
    fixtureAssert($targetReport['user']['id'] === 'configured-target-user', 'The target report must retain the configured user.');
    fixtureAssert($targetReport['metaData']['configured']['retained'] === true, 'The target report must retain configured metadata.');
    fixtureAssert($targetReport['metaData']['yiiLog'] === [
        'level' => 'error',
        'category' => 'fixture.category',
        'timestamp' => $timestamp,
        'traces' => $traces,
    ], 'The target report must retain structured Yii log metadata.');

    $config = require dirname(__DIR__, 2) . '/src/config.php';
    fixtureAssert($config['filters'] === ['password'], 'The shipped filter configuration must match the settings-model default.');

    echo "Bugsnag default callbacks security fixture passed.\n";
}
