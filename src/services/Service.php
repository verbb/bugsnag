<?php
namespace verbb\bugsnag\services;

use verbb\bugsnag\Bugsnag;
use verbb\bugsnag\models\Settings;

use Craft;
use craft\base\Component;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\Client;
use Bugsnag\Report;

class Service extends Component
{
    // Properties
    // =========================================================================

    /** @var Settings */
    private $settings;

    /** @var Client */
    private $bugsnag;

    public $metadata = [];


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        $this->settings = Bugsnag::$plugin->getSettings();

        if ($this->isEnabled()) {
            $this->bugsnag = Client::make($this->settings->serverApiKey);

            $this->bugsnag->setReleaseStage($this->settings->releaseStage);
            $this->bugsnag->setAppVersion($this->settings->appVersion);
            $this->bugsnag->setNotifyReleaseStages($this->settings->notifyReleaseStages);

            if (!empty($this->settings->filters)) {
                $this->bugsnag->setFilters($this->settings->filters);
            }

            $this->bugsnag->registerCallback(function($report) {
                if (!empty($this->settings->metaData)) {
                    $report->setMetaData($this->settings->metaData);
                }

                if ($user = Craft::$app->getUser()->getIdentity()) {
                    $report->setUser([
                        'id' => $user->id,
                        'name' => $user->getName(),
                        'email' => $user->email,
                    ]);
                }
            });
        }
    }

    public function breadcrumb(string $text = '', string $type = Breadcrumb::MANUAL_TYPE, array $metaData = []): bool
    {
        if (empty($text)) {
            return false;
        }

        $this->bugsnag->leaveBreadcrumb($text, $type, $metaData);

        return true;
    }

    public function metadata(array $metadata = []): Service
    {
        $this->metadata = array_merge($metadata, $metadata);

        return $this;
    }

    public function handleException($exception)
    {
        if (!$this->isEnabled()) {
            return true;
        }

        $this->bugsnag->notifyException($exception);
    }

    public function getClient(): Client
    {
        return $this->bugsnag;
    }

    public function isEnabled(): bool
    {
        return $this->settings->enabled && !empty($this->settings->serverApiKey);
    }
}
