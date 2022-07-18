<?php
namespace verbb\bugsnag\models;

use verbb\bugsnag\Bugsnag;

use Craft;
use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;
use craft\helpers\App;

use yii\web\NotFoundHttpException;

use function is_callable;

class Settings extends Model
{
    // Properties
    // =========================================================================

    public $enabled = true;
    public $serverApiKey = '';
    public $browserApiKey = '';
    public $releaseStage = 'production';
    public $appVersion = '';
    public $notifyReleaseStages = ['production'];
    public $filters = ['password'];
    public $blacklist = [];
    public $metaData = [];


    // Public Methods
    // =========================================================================

    public function getBlacklist(): array
    {
        if (!is_array($this->blacklist)) {
            return [];
        }

        $blacklist = array_map(function($row) {
            if (isset($row['class']) && is_callable($row['class'])) {
                $row['class'] = 'Advanced check set through config file';
            }

            return $row;
        }, $this->blacklist);

        return array_filter($blacklist);
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

    public function behaviors(): array
    {
        return [
            'parser' => [
                'class' => EnvAttributeParserBehavior::class,
                'attributes' => ['serverApiKey'],
            ],
        ];
    }

    public function defineRules(): array
    {
        $rules = parent::defineRules();

        $rules[] = [['serverApiKey'], 'required'];

        return $rules;
    }

    public function getBrowserConfig(): array
    {
        $data = [
            'apiKey' => $this->getBrowserApiKey(),
        ];

        if (!empty($this->releaseStage)) {
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

    public function getServerApiKey()
    {
        return $this->parseValue($this->serverApiKey);
    }

    public function getBrowserApiKey()
    {
        return $this->parseValue($this->browserApiKey);
    }

    public function getReleaseStage()
    {
        return $this->parseValue($this->releaseStage);
    }

    public function getMetadata(): array
    {
        return array_merge($this->metaData, Bugsnag::$plugin->getService()->metadata);
    }


    // Private Methods
    // =========================================================================

    private function parseValue($value)
    {
        return App::parseEnv($value);
    }
}
