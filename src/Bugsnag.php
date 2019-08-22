<?php
/**
 * Bugsnag plugin for Craft CMS 3.x
 *
 * Log Craft errors/exceptions to Bugsnag.
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

namespace superbig\bugsnag;

use craft\events\ExceptionEvent;
use craft\helpers\UrlHelper;
use craft\web\ErrorHandler;
use superbig\bugsnag\services\BugsnagService;
use superbig\bugsnag\models\Settings;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;

use yii\base\Event;

/**
 * Class Bugsnag
 *
 * @author    Superbig
 * @package   Bugsnag
 * @since     2.0.0
 *
 * @property  BugsnagService $bugsnagService
 * @method  Settings getSettings()
 */
class Bugsnag extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Bugsnag
     */
    public static $plugin;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init ()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ( $event->plugin === $this && !Craft::$app->getRequest()->isConsoleRequest ) {
                    Craft::$app->response->redirect(UrlHelper::cpUrl('settings/plugins/bugsnag'))->send();
                }
            }
        );

        Event::on(
            ErrorHandler::className(),
            ErrorHandler::EVENT_BEFORE_HANDLE_EXCEPTION,
            function (ExceptionEvent $event) {
                $settings = $this->getSettings();

                if (is_array($settings->blacklist)) {
                    foreach ($settings->blacklist as $config) {
                        if (isset($config['class'])) {
                            if (is_callable($config['class'])) {
                                $result = $config['class']($event->exception);
                                if (!$result) {
                                    return;
                                }
                            }
                            else {
                                if ($event->exception instanceof $config['class']) {
                                    return;
                                }
                            }
                        }
                    }
                }

                $this->bugsnagService->handleException($event->exception);
            }
        );

        Craft::info(
            Craft::t(
                'bugsnag',
                '{name} plugin loaded',
                [ 'name' => $this->name ]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel ()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml (): string
    {
        return Craft::$app->view->renderTemplate(
            'bugsnag/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
