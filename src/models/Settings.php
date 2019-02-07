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
    public $notifyReleaseStages = [ 'production' ];

    /**
     * @var array
     */
    public $filters = [];

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

    /**
     * @inheritdoc
     */
    public function rules ()
    {
        return [
            [ [ 'serverApiKey' ], 'required' ],
            //[ 'someAttribute', 'string' ],
            //[ 'someAttribute', 'default', 'value' => 'Some Default' ],
        ];
    }
}
