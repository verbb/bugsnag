<?php
namespace verbb\bugsnag\log;

use verbb\bugsnag\Bugsnag;
use verbb\bugsnag\helpers\Bot;

use Bugsnag\Client;

use Craft;
use craft\helpers\App;

use Throwable;

use yii\log\Logger;
use yii\log\Target;

class BugsnagTarget extends Target
{
    // Properties
    // =========================================================================

    public bool|string|null $serverApiKey = '';
    public string $releaseStage = 'production';
    public string $appVersion = '';
    public array $notifyReleaseStages = ['production'];
    public array $filters = ['password'];
    public bool|string $ignoreBots = false;
    public array $metaData = [];
    public array $exceptCodes = [403, 404];
    public array $exceptPatterns = [];
    public bool $reportExceptions = false;
    public ?Client $client = null;

    private ?Client $_bugsnag = null;


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        if (is_string($this->enabled)) {
            $this->enabled = $this->_parseBooleanEnv($this->enabled);
        }

        if ($this->levels === 0 || $this->levels === []) {
            $this->levels = ['error', 'warning'];
        }

        foreach ($this->exceptCodes as $code) {
            $this->except[] = 'yii\web\HttpException:' . $code;
        }

        parent::init();

        $apiKey = $this->_parseEnv($this->serverApiKey);

        if (!$this->enabled || (empty($apiKey) && !$this->client)) {
            $this->enabled = false;

            return;
        }

        $this->_bugsnag = $this->client ?? Client::make($apiKey);
        $this->_bugsnag->setReleaseStage($this->_parseEnv($this->releaseStage) ?: 'production');
        $this->_bugsnag->setAppVersion($this->_parseEnv($this->appVersion) ?: '');
        $this->_bugsnag->setNotifyReleaseStages($this->notifyReleaseStages);

        if (!empty($this->filters)) {
            $this->_bugsnag->setRedactedKeys($this->filters);
        }
    }

    public function export(): void
    {
        if (!$this->_bugsnag || ($this->_getIgnoreBots() && Bot::isCurrentRequestCrawler())) {
            return;
        }

        foreach ($this->messages as $message) {
            if ($this->_shouldSkipMessage($message)) {
                continue;
            }

            [$text, $level, $category] = $message;
            $levelName = $this->_getLevelName($level);
            $severity = $this->_getSeverity($levelName);

            if ($text instanceof Throwable) {
                $this->_bugsnag->notifyException($text, function($report) use ($message, $category, $levelName, $severity) {
                    $this->_customizeReport($report, $message, $category, $levelName, $severity);
                });

                continue;
            }

            $this->_bugsnag->notifyError($this->_getErrorName($category, $levelName), $this->_formatText($text), function($report) use ($message, $category, $levelName, $severity) {
                $this->_customizeReport($report, $message, $category, $levelName, $severity);
            });
        }
    }


    // Private Methods
    // =========================================================================

    private function _shouldSkipMessage(array $message): bool
    {
        $text = $message[0] ?? null;

        if ($text instanceof Throwable && !$this->reportExceptions) {
            return true;
        }

        if (empty($this->exceptPatterns)) {
            return false;
        }

        $formattedText = $this->_formatText($text);

        foreach ($this->exceptPatterns as $pattern) {
            if ($pattern !== '' && str_contains($formattedText, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function _customizeReport($report, array $message, string $category, string $levelName, string $severity): void
    {
        $metadata = array_replace_recursive($this->metaData, Bugsnag::$plugin?->getService()->metadata ?? [], [
            'yiiLog' => [
                'level' => $levelName,
                'category' => $category,
                'timestamp' => $message[3] ?? null,
                'traces' => $message[4] ?? [],
            ],
        ]);

        $report->setSeverity($severity);
        $report->setMetaData($metadata);

        $userComponent = Craft::$app->has('user', true) ? Craft::$app->get('user') : null;

        if ($userComponent && method_exists($userComponent, 'getIdentity') && $user = $userComponent->getIdentity()) {
            $report->setUser([
                'id' => $user->id,
                'name' => $user->getName(),
                'email' => $user->email,
            ]);
        }
    }

    private function _formatText(mixed $text): string
    {
        if ($text instanceof Throwable) {
            return $text->getMessage();
        }

        if (is_string($text)) {
            return $text;
        }

        return print_r($text, true);
    }

    private function _getErrorName(string $category, string $levelName): string
    {
        $name = preg_replace('/[^A-Za-z0-9_]+/', '_', $category) ?: 'YiiLog';

        return trim($name, '_') . '_' . ucfirst($levelName);
    }

    private function _getLevelName(int $level): string
    {
        return match ($level) {
            Logger::LEVEL_ERROR => 'error',
            Logger::LEVEL_WARNING => 'warning',
            Logger::LEVEL_INFO => 'info',
            Logger::LEVEL_TRACE => 'trace',
            Logger::LEVEL_PROFILE_BEGIN, Logger::LEVEL_PROFILE_END => 'profile',
            default => 'unknown',
        };
    }

    private function _getSeverity(string $levelName): string
    {
        return match ($levelName) {
            'error' => 'error',
            'warning' => 'warning',
            default => 'info',
        };
    }

    private function _parseEnv(mixed $value): mixed
    {
        if (is_string($value)) {
            return App::parseEnv($value);
        }

        return $value;
    }

    private function _parseBooleanEnv(mixed $value): bool
    {
        return App::parseBooleanEnv($value) ?? false;
    }

    private function _getIgnoreBots(): bool
    {
        return $this->_parseBooleanEnv($this->ignoreBots);
    }
}
