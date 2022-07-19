<?php
namespace verbb\bugsnag\models;

use verbb\bugsnag\Bugsnag;

use Craft;
use craft\base\Model;
use craft\helpers\App;

class Settings extends Model
{
    // Properties
    // =========================================================================

    public bool $enabled = true;
    public string $serverApiKey = '';
    public string $browserApiKey = '';
    public string $releaseStage = 'production';
    public string $appVersion = '';
    public array $notifyReleaseStages = ['production'];
    public array $filters = ['password'];
    public array $blacklist = [];
    public array $metaData = [];


    // Public Methods
    // =========================================================================

    public function getBlacklist(): array
    {
        return array_filter(array_map(function($row) {
            if (isset($row['class']) && is_callable($row['class'])) {
                $row['class'] = 'Advanced check set through config file';
            }

            return $row;
        }, $this->blacklist));
    }

    public function isValidException($exception): bool
    {
        $isValid = true;

        foreach ($this->blacklist as $config) {
            if (isset($config['class']) && is_callable($config['class'])) {
                $isValid = $config['class']($exception);
            }
        }

        return $isValid;
    }

    public function getBrowserConfig(): array
    {
        $data = [
            'apiKey' => $this->getBrowserApiKey(),
        ];

        if (!empty($this->getReleaseStage())) {
            $data['releaseStage'] = $this->getReleaseStage();
        }

        if (!empty($this->notifyReleaseStages)) {
            $data['enabledReleaseStages'] = $this->notifyReleaseStages;
        }

        if (!empty($this->getMetadata())) {
            $data['metadata'] = $this->getMetadata();
        }

        if ($currentUser = Craft::$app->getUser()->getIdentity()) {
            $data['user'] = [
                'id' => $currentUser->id,
                'name' => $currentUser->fullName,
                'email' => $currentUser->email,
            ];
        }

        return $data;
    }

    public function getEnabled(): bool|string|null
    {
        return App::parseEnv($this->enabled);
    }

    public function getServerApiKey(): bool|string|null
    {
        return App::parseEnv($this->serverApiKey);
    }

    public function getBrowserApiKey(): bool|string|null
    {
        return App::parseEnv($this->browserApiKey);
    }

    public function getReleaseStage(): bool|string|null
    {
        return App::parseEnv($this->releaseStage);
    }

    public function getMetadata(): array
    {
        return array_merge($this->metaData, Bugsnag::$plugin->getService()->metadata);
    }
}
