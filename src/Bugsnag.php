<?php
namespace verbb\bugsnag;

use verbb\bugsnag\base\PluginTrait;
use verbb\bugsnag\models\Settings;
use verbb\bugsnag\variables\BugsnagVariable;

use Craft;
use craft\base\Plugin;
use craft\events\ExceptionEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\web\ErrorHandler;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;

use yii\base\Event;

class Bugsnag extends Plugin
{
    // Properties
    // =========================================================================

    public bool $hasCpSettings = true;
    public string $schemaVersion = '2.0.0';


    // Traits
    // =========================================================================

    use PluginTrait;


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        parent::init();

        self::$plugin = $this;

        $this->_registerVariables();
        $this->_registerEventHandlers();

        if (Craft::$app->getRequest()->getIsCpRequest()) {
            $this->_registerCpRoutes();
        }
    }

    public function getPluginName(): string
    {
        return Craft::t('bugsnag', 'Bugsnag');
    }

    public function getSettingsResponse(): mixed
    {
        return Craft::$app->getResponse()->redirect(UrlHelper::cpUrl('bugsnag/settings'));
    }


    // Protected Methods
    // =========================================================================

    protected function createSettingsModel(): Settings
    {
        return new Settings();
    }


    // Private Methods
    // =========================================================================

    private function _registerVariables(): void
    {
        Event::on(CraftVariable::class, CraftVariable::EVENT_INIT, function(Event $event) {
            $event->sender->set('bugsnag', BugsnagVariable::class);
        });
    }

    private function _registerCpRoutes(): void
    {
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $event) {
            $event->rules = array_merge($event->rules, [
                'bugsnag/settings' => 'bugsnag/base/settings',
            ]);
        });
    }

    private function _registerEventHandlers(): void
    {
        Event::on(ErrorHandler::class, ErrorHandler::EVENT_BEFORE_HANDLE_EXCEPTION, function(ExceptionEvent $event) {
            $settings = $this->getSettings();

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

            $this->getService()->handleException($event->exception);
        });
    }

}
