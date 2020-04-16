<?php
/**
 * Bugsnag plugin for Craft CMS 3.x
 *
 * Log Craft errors/exceptions to Bugsnag.
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

namespace superbig\bugsnag\services;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\Client;
use Bugsnag\Report;
use craft\helpers\ArrayHelper;
use superbig\bugsnag\Bugsnag;

use Craft;
use craft\base\Component;
use craft\elements\User;
use superbig\bugsnag\models\Settings;

/**
 * @author    Superbig
 * @package   Bugsnag
 * @since     2.0.0
 */
class BugsnagService extends Component
{
    // Public Methods
    // =========================================================================

    /** @var Settings */
    private $settings;

    /** @var Client */
    private $bugsnag;

    public $metadata = [];

    public function init ()
    {
        $this->settings = Bugsnag::$plugin->getSettings();

        if ( $this->isEnabled() ) {
            $this->bugsnag = Client::make($this->settings->serverApiKey);

            $this->bugsnag->setReleaseStage($this->settings->releaseStage);
            $this->bugsnag->setAppVersion($this->settings->appVersion);
            $this->bugsnag->setNotifyReleaseStages($this->settings->notifyReleaseStages);

            if ( !empty($this->settings->filters) ) {
                $this->bugsnag->setFilters($this->settings->filters);
            }

            $this->bugsnag->registerCallback(function (/** @var Report $report */
                $report) {

                if ( !empty($this->settings->metaData) ) {
                    $report->setMetaData($this->settings->metaData);
                }

                if ( $user = Craft::$app->getUser()->getIdentity() ) {
                    $report->setUser([
                        'id'    => $user->id,
                        'name'  => $user->getName(),
                        'email' => $user->email,
                    ]);
                }
            });
        }

    }

    /**
     * @param string $text
     * @param string $type
     * @param array  $metaData
     *
     * @return bool
     */
    public function breadcrumb ($text = '', $type = Breadcrumb::MANUAL_TYPE, $metaData = [])
    {
        if ( empty($text) ) {
            return false;
        }

        $this->bugsnag->leaveBreadcrumb($text, $type, $metaData);
    }

    public function metadata(array $metadata = [])
    {
        $this->metadata = array_merge($metadata, $metadata);

        return $this;
    }

    /**
     * @param $exception
     *
     * @return bool | void
     */
    public function handleException ($exception)
    {
        if ( !$this->isEnabled() ) {
            return true;
        }

        $this->bugsnag->notifyException($exception);
    }

    /**
     * @return Client
     */
    public function getClient () : Client
    {
        return $this->bugsnag;
    }

    public function isEnabled ()
    {
        return $this->settings->enabled && !empty($this->settings->serverApiKey);
    }
}
