<?php
namespace verbb\bugsnag\controllers;

use verbb\bugsnag\Bugsnag;

use craft\web\Controller;

use yii\web\Response;

class BaseController extends Controller
{
    // Public Methods
    // =========================================================================

    public function actionSettings(): Response
    {
        $settings = Bugsnag::$plugin->getSettings();

        return $this->renderTemplate('bugsnag/settings', [
            'settings' => $settings,
        ]);
    }

}