<?php
namespace verbb\bugsnag\assetbundles\frontend;

use verbb\bugsnag\Bugsnag;

use Craft;
use craft\helpers\Json;
use craft\web\AssetBundle;
use craft\web\View;

class FrontEndAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    public function init(): void
    {
        $settings = Bugsnag::$plugin->getSettings();

        if (!$settings->getEnabled() || empty($settings->getBrowserApiKey()) || $settings->shouldIgnoreCurrentRequest()) {
            return;
        }

        $this->js[] = [
            $settings->getBrowserCdnUrl(),
            'position' => View::POS_HEAD,
        ];

        // Include this wrapper since bugsnag.js might be blocked by adblockers.  We don't want to completely die if so.
        $encodedSettings = Json::encode($settings->getBrowserConfig());
        $js = "Bugsnag.start({$encodedSettings});";

        Craft::$app->getView()->registerJs($js, View::POS_HEAD);

        parent::init();
    }
}
