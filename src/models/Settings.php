<?php
/**
 * Bugsnag plugin for Craft CMS 3.x
 *
 * Log Craft errors/exceptions to Bugsnag.
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

namespace superbig\bugsnag\models;

use craft\behaviors\EnvAttributeParserBehavior;
use superbig\bugsnag\Bugsnag;

use Craft;
use craft\base\Model;

/**
 * @author    Superbig
 * @package   Bugsnag
 * @since     2.0.0
 *
 * @property boolean $enabled
 * @property string  $browserApiKey
 * @property string  $serverApiKey
 * @property string  $releaseStage
 * @property array   $notifyReleaseStages
 * @property array   $filters
 * @property array   $blacklist
 * @property array   $metaData
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    public $enabled             = true;
    public $serverApiKey        = '';
    public $browserApiKey       = '';
    public $releaseStage        = 'production';
    public $appVersion          = '';
    public $notifyReleaseStages = ['production'];
    public $filters             = ['password'];
    public $blacklist           = [];
    public $metaData            = [];

    // Public Methods
    // =========================================================================

    public function getBlacklist()
    {
        if (!is_array($this->blacklist)) {
            return [];
        }

        $blacklist = array_map(function($row) {
            if (isset($row['class']) && \is_callable($row['class'])) {
                $row['class'] = 'Advanced check set through config file';
            }

            return $row;
        }, $this->blacklist);

        return array_filter($blacklist);
    }

    public function isValidException($exception): bool
    {
        /**
         * @var \yii\web\NotFoundHttpException $exception
         */
        $isValid = true;

        foreach ($this->blacklist as $config) {
            if (isset($config['class']) && \is_callable($config['class'])) {
                $isValid = $config['class']($exception);
            }
        }

        return $isValid;
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'parser' => [
                'class'      => EnvAttributeParserBehavior::class,
                'attributes' => ['serverApiKey'],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['serverApiKey'], 'required'],
        ];
    }

    public function getBrowserConfig()
    {
        $data = [
            'apiKey'       => $this->getBrowserApiKey(),
            'releaseStage' => $this->getReleaseStage(),
        ];

        if (!empty($this->notifyReleaseStages)) {
            $data['enabledReleaseStages'] = $this->notifyReleaseStages;
        }

        if (!empty($this->getMetadata())) {
            $data['metadata'] = $this->getMetadata();
        }

        if ($currentUser = Craft::$app->getUser()->getIdentity()) {
            $data['user'] = [
                'id'    => $currentUser->id,
                'name'  => $currentUser->fullName,
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

    public function getMetadata()
    {
        return array_merge($this->metaData, Bugsnag::$plugin->getService()->metadata);
    }

    private function parseValue($value)
    {
        return Craft::parseEnv($value);
    }
}
