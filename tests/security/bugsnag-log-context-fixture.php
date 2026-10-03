<?php

namespace verbb\bugsnag {
    final class Bugsnag
    {
        public static mixed $plugin = null;
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
        public static function resolve(mixed $user): ?array
        {
            return null;
        }
    }
}

namespace {
    use Bugsnag\Client;

    use verbb\bugsnag\log\BugsnagTarget;

    use yii\log\Logger;

    $vendorPath = getenv('BUGSNAG_CRAFT_VENDOR') ?: dirname(__DIR__, 2) . '/vendor';

    if (!is_file($vendorPath . '/autoload.php')) {
        throw new RuntimeException('Set BUGSNAG_CRAFT_VENDOR to a Craft 5 vendor directory with the Bugsnag PHP SDK.');
    }

    require $vendorPath . '/autoload.php';
    require $vendorPath . '/yiisoft/yii2/Yii.php';
    require $vendorPath . '/craftcms/cms/src/Craft.php';
    require dirname(__DIR__, 2) . '/src/log/BugsnagTarget.php';

    final class BugsnagLogContextFixtureReport
    {
        public string $severity = '';
        public array $metadata = [];

        public function setSeverity(string $severity): void
        {
            $this->severity = $severity;
        }

        public function setMetaData(array $metadata): void
        {
            $this->metadata = $metadata;
        }

        public function setUser(array $user): void
        {
        }
    }

    final class BugsnagLogContextFixtureClient extends Client
    {
        public array $notifications = [];

        public function __construct()
        {
        }

        public function setReleaseStage($releaseStage)
        {
            return $this;
        }

        public function setAppVersion($appVersion)
        {
            return $this;
        }

        public function setNotifyReleaseStages($notifyReleaseStages = null)
        {
            return $this;
        }

        public function notifyError($name, $message, $callback = null)
        {
            $report = new BugsnagLogContextFixtureReport();

            if ($callback) {
                $callback($report);
            }

            $this->notifications[] = [
                'name' => $name,
                'message' => $message,
                'report' => $report,
            ];
        }
    }

    final class BugsnagLogContextFixtureTarget extends BugsnagTarget
    {
        public function contextMessage(): string
        {
            return $this->getContextMessage();
        }
    }

    function fixtureAssert(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new RuntimeException($message);
        }
    }

    $secrets = [
        'fixture-query-secret',
        'fixture-body-secret',
        'fixture-upload-secret',
        'fixture-cookie-secret',
        'fixture-session-secret',
        'fixture-authorization-secret',
        'fixture-url-secret',
    ];
    $GLOBALS['_GET'] = ['search' => $secrets[0]];
    $GLOBALS['_POST'] = ['payload' => $secrets[1]];
    $GLOBALS['_FILES'] = ['upload' => ['tmp_name' => $secrets[2]]];
    $GLOBALS['_COOKIE'] = ['CraftSessionId' => $secrets[3]];
    $GLOBALS['_SESSION'] = ['private' => $secrets[4]];
    $GLOBALS['_SERVER'] = [
        'HTTP_AUTHORIZATION' => 'Bearer ' . $secrets[5],
        'HTTP_COOKIE' => 'CraftSessionId=' . $secrets[3],
        'REQUEST_URI' => '/fixture?credential=' . $secrets[6],
    ];

    $client = new BugsnagLogContextFixtureClient();
    $target = new BugsnagLogContextFixtureTarget([
        'client' => $client,
        'filters' => [],
        'metaData' => ['custom' => ['kept' => true]],
        'user' => false,
    ]);
    fixtureAssert($target->logVars === [], 'Bugsnag targets must not inherit Yii request globals by default.');
    fixtureAssert($target->contextMessage() === '', 'The default target must not generate a synthetic request-context message.');

    $timestamp = 1234567890.25;
    $traces = [['file' => '/fixture/source.php', 'line' => 42]];
    $target->collect([
        ['Ordinary fixture failure.', Logger::LEVEL_ERROR, 'fixture.category', $timestamp, $traces, 1024],
    ], true);

    fixtureAssert(count($client->notifications) === 1, 'One application log must produce one Bugsnag notification without a synthetic globals report.');
    $notification = $client->notifications[0];
    fixtureAssert($notification['name'] === 'fixture_category_Error', 'The existing Yii error name must remain intact.');
    fixtureAssert($notification['message'] === 'Ordinary fixture failure.', 'The original application log text must remain intact.');
    fixtureAssert($notification['report']->severity === 'error', 'The existing severity mapping must remain intact.');
    fixtureAssert($notification['report']->metadata['custom']['kept'] === true, 'Configured custom metadata must remain intact.');
    fixtureAssert($notification['report']->metadata['yiiLog'] === [
        'level' => 'error',
        'category' => 'fixture.category',
        'timestamp' => $timestamp,
        'traces' => $traces,
    ], 'Structured Yii log metadata must remain intact.');

    $serializedNotifications = json_encode($client->notifications);

    foreach ($secrets as $secret) {
        fixtureAssert(!str_contains($serializedNotifications, $secret), "The Bugsnag notification must not contain $secret.");
    }

    $manualTarget = new BugsnagLogContextFixtureTarget([
        'enabled' => false,
        'logVars' => ['_GET.safe'],
    ]);
    $GLOBALS['_GET'] = [
        'safe' => 'explicit-context-value',
        'private' => 'excluded-context-value',
    ];
    $manualContext = $manualTarget->contextMessage();
    fixtureAssert(str_contains($manualContext, 'explicit-context-value'), 'A trusted direct target must retain explicit narrow logVars configuration.');
    fixtureAssert(!str_contains($manualContext, 'excluded-context-value'), 'An explicit narrow logVars allowlist must exclude unselected values.');

    echo "Bugsnag Yii log context security fixture passed.\n";
}
