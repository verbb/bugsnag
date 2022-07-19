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

        if (!$settings->getEnabled() || empty($settings->getBrowserApiKey())) {
            return;
        }

        $filePath = 'https://d2wy8f7a9ursnm.cloudfront.net/v7.0.0/bugsnag.min.js';

        $this->js[] = [
            $filePath,
            'position' => View::POS_HEAD,
        ];

        // Include this wrapper since bugsnag.js might be blocked by adblockers.  We don't want to completely die if so.
        $encodedSettings = Json::encode($settings->getBrowserConfig());
        $js = "Bugsnag.start({$encodedSettings});";

        Craft::$app->getView()->registerJs($js, View::POS_HEAD);

        parent::init();
    }
}
