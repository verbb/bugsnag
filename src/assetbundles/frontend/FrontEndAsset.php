<?php
/**
 * Bugsnag plugin for Craft CMS 3.x
 *
 * Log Craft errors/exceptions to Bugsnag.
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

namespace superbig\bugsnag\assetbundles\frontend;

use superbig\bugsnag\Bugsnag;

use Craft;
use craft\helpers\Json;
use craft\web\AssetBundle;
use craft\web\View;

/**
 * @author    Superbig
 * @package   Bugsnag
 * @since     2.0.0
 */
class FrontEndAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $settings = Bugsnag::$plugin->getSettings();

        if (!$settings->enabled || empty($settings->getBrowserApiKey())) {
            return;
        }

        $filePath = 'https://d2wy8f7a9ursnm.cloudfront.net/v6/bugsnag.min.js';

        $this->js[] = [
            $filePath,
            'position' => View::POS_HEAD,
        ];

        // Include this wrapper since bugsnag.js might be blocked by adblockers.  We don't want to completely die if so.
        $jsSettings = [
            'apiKey'       => $settings->getBrowserApiKey(),
            'releaseStage' => $settings->getReleaseStage(),
        ];

        if (!empty($settings->notifyReleaseStages)) {
            $jsSettings['notifyReleaseStages'] = $settings->notifyReleaseStages;
        }

        $encodedSettings = Json::encode($jsSettings);
        $js              = "var bugsnag = bugsnag || function() { console.log(arguments) }; window.bugsnagClient = bugsnag({$encodedSettings});";

        if (!Craft::$app->getUser()->getIsGuest()) {
            $currentUser = Craft::$app->getUser()->getIdentity();
            $userInfo    = Json::htmlEncode([
                'id'    => $currentUser->id,
                'name'  => $currentUser->fullName,
                'email' => $currentUser->email,
            ]);

            $js .= "window.bugsnagClient.user = $userInfo;";
        }

        Craft::$app->view->registerJs($js, View::POS_BEGIN);

        parent::init();
    }
}
