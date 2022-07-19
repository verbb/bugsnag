<?php
namespace verbb\bugsnag;

use verbb\bugsnag\base\PluginTrait;
use verbb\bugsnag\models\Settings;
use verbb\bugsnag\variables\BugsnagVariable;

use Craft;
use craft\base\Plugin;
use craft\events\ExceptionEvent;
use craft\events\PluginEvent;
use craft\helpers\UrlHelper;
use craft\services\Plugins;
use craft\web\ErrorHandler;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;
use yii\base\InvalidConfigException;

class Bugsnag extends Plugin
{
    // Properties
    // =========================================================================

    public $schemaVersion = '2.0.0';
    public $hasCpSettings = true;


    // Traits
    // =========================================================================

    use PluginTrait;


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        parent::init();

        self::$plugin = $this;

        $this->_setPluginComponents();
        $this->_setLogging();
        $this->_registerVariables();
        $this->_registerCraftEventListeners();
    }

    public function getSettingsResponse()
    {
        Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('bugsnag/settings'));
    }


    // Protected Methods
    // =========================================================================

    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }


    // Private Methods
    // =========================================================================

    private function _registerVariables()
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $event->sender->set('bugsnag', BugsnagVariable::class);
        });
    }

    private function _registerCraftEventListeners()
    {
        Event::on(ErrorHandler::class, ErrorHandler::EVENT_BEFORE_HANDLE_EXCEPTION, function(ExceptionEvent $event) {
            $settings = $this->getSettings();

            if (is_array($settings->blacklist)) {
                foreach ($settings->blacklist as $config) {
                    if (isset($config['class'])) {
                        if (is_callable($config['class'])) {
                            $result = $config['class']($event->exception);

                            if (!$result) {
                                return;
                            }
                        } else if ($event->exception instanceof $config['class']) {
                            return;
                        }
                    }
                }
            }

            $this->getService()->handleException($event->exception);
        });
    }

}
