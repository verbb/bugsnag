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

        if (!$settings->enabled) {
            return;
        }

        $filePath = '//d2wy8f7a9ursnm.cloudfront.net/v6/bugsnag.min.js';

        $this->js[] = [
            $filePath, 
            'data-apikey' => $settings->serverApiKey,
            'data-releasestage' => $settings->releaseStage,
            'position' => View::POS_HEAD,
        ];

        // Include this wrapper since bugsnag.js might be blocked by adblockers.  We don't want to completely die if so.
        $js = 'var Bugsnag = Bugsnag || {};';

        if (!Craft::$app->user->isGuest) {
            $currentUser = Craft::$app->user->identity;

            $userInfo = Json::htmlEncode([
                'id' => $currentUser->id,
                'name' => $currentUser->fullName,
                'email' => $currentUser->email,
            ]);

            $js .= "Bugsnag.user = $userInfo;";
        }

        if (!empty($settings->notifyReleaseStages)) {
            $releaseStages = Json::htmlEncode($settings->notifyReleaseStages);

            $js .= "Bugsnag.notifyReleaseStages = $releaseStages;";
        }

        Craft::$app->view->registerJs($js, View::POS_BEGIN);

        parent::init();
    }
}
