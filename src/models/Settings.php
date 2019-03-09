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

use superbig\bugsnag\Bugsnag;

use Craft;
use craft\base\Model;

/**
 * @author    Superbig
 * @package   Bugsnag
 * @since     2.0.0
 *
 * @property string $apiKey
 * @property string $releaseStage
 * @property array  $notifyReleaseStages
 * @property array  $filters
 * @property array  $blacklist
 * @property array  $metaData
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var boolean
     */
    public $enabled = true;

    /**
     * @var string
     */
    public $serverApiKey = '';

    /**
     * @var string
     */
    public $releaseStage = 'production';

    /**
     * @var string
     */
    public $appVersion = '';

    /**
     * @var array
     */
    public $notifyReleaseStages = ['production'];

    /**
     * @var array
     */
    public $filters = ['password'];

    /**
     * @var array
     */
    public $blacklist = [];

    /**
     * @var array
     */
    public $metaData = [];

    // Public Methods
    // =========================================================================

    public function getBlacklist()
    {
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
    public function rules()
    {
        return [
            [['serverApiKey'], 'required'],
            //[ 'someAttribute', 'string' ],
            //[ 'someAttribute', 'default', 'value' => 'Some Default' ],
        ];
    }
}
