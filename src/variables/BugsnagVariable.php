<?php
namespace verbb\bugsnag\variables;

use verbb\bugsnag\Bugsnag;
use verbb\bugsnag\services\Service;

use craft\helpers\Json;
use craft\helpers\Template;

use Twig\Markup;

class BugsnagVariable
{
    // Public Methods
    // =========================================================================

    public function getPluginName(): string
    {
        return Bugsnag::$plugin->getPluginName();
    }

    public function metadata(array $data = []): Service
    {
        return Bugsnag::$plugin->getService()->metadata($data);
    }

    public function getBrowserConfig($asJson = true): Markup|array
    {
        $config = Bugsnag::$plugin->getSettings()->getBrowserConfig();

        if ($asJson) {
            return Template::raw(Json::htmlEncode($config));
        }

        return $config;
    }

    public function handleException($exception): void
    {
        Bugsnag::$plugin->getService()->handleException($exception);
    }
}
