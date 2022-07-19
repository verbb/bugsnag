<?php
namespace verbb\bugsnag\services;

use verbb\bugsnag\Bugsnag;
use verbb\bugsnag\models\Settings;

use Craft;
use craft\base\Component;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\Client;

class Service extends Component
{
    // Properties
    // =========================================================================

    public array $metadata = [];

    private ?Settings $settings = null;
    private ?Client $bugsnag = null;


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        $this->settings = Bugsnag::$plugin->getSettings();

        if ($this->isEnabled()) {
            $this->bugsnag = Client::make($this->settings->getServerApiKey());

            $this->bugsnag->setReleaseStage($this->settings->getReleaseStage());
            $this->bugsnag->setAppVersion($this->settings->appVersion);
            $this->bugsnag->setNotifyReleaseStages($this->settings->notifyReleaseStages);

            if (!empty($this->settings->filters)) {
                $this->bugsnag->setRedactedKeys($this->settings->filters);
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

    public function handleException($exception): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $this->bugsnag->notifyException($exception);
    }

    public function getClient(): Client
    {
        return $this->bugsnag;
    }

    public function isEnabled(): bool
    {
        return $this->settings->getEnabled() && !empty($this->settings->getServerApiKey());
    }
}
