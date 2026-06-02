<?php
namespace verbb\bugsnag;

use verbb\bugsnag\base\PluginTrait;
use verbb\bugsnag\log\BugsnagTarget;
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

        $this->_registerLogTarget();
        $this->_registerVariables();
        $this->_registerEventHandlers();
        $this->_registerCommerceEventHandlers();

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

            if ($settings->shouldIgnoreCurrentRequest()) {
                return;
            }

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

    private function _registerCommerceEventHandlers(): void
    {
        $settings = $this->getSettings();

        if (!$settings->getEnabled() || (!$settings->getCommerceAutoBreadcrumbs() && !$settings->getCommerceAutoMetadata())) {
            return;
        }

        $orderClass = 'craft\\commerce\\elements\\Order';
        $transactionsClass = 'craft\\commerce\\services\\Transactions';

        if (class_exists($orderClass)) {
            $eventName = defined($orderClass . '::EVENT_AFTER_SAVE') ? constant($orderClass . '::EVENT_AFTER_SAVE') : 'afterSave';

            Event::on($orderClass, $eventName, function(Event $event) {
                $this->getService()->handleCommerceOrder($event->sender);
            });
        }

        if (class_exists($transactionsClass)) {
            $eventName = defined($transactionsClass . '::EVENT_AFTER_SAVE_TRANSACTION') ? constant($transactionsClass . '::EVENT_AFTER_SAVE_TRANSACTION') : 'afterSaveTransaction';

            Event::on($transactionsClass, $eventName, function(Event $event) {
                if (isset($event->transaction)) {
                    $this->getService()->handleCommerceTransaction($event->transaction);
                }
            });
        }
    }

    private function _registerLogTarget(): void
    {
        $settings = $this->getSettings();

        if (!$settings->getEnabled() || !$settings->getLogTargetEnabled() || empty($settings->getServerApiKey())) {
            return;
        }

        $log = Craft::$app->getLog();

        if (isset($log->targets['bugsnag'])) {
            return;
        }

        $log->targets['bugsnag'] = Craft::createObject([
            'class' => BugsnagTarget::class,
            'serverApiKey' => $settings->getServerApiKey(),
            'releaseStage' => $settings->getReleaseStage(),
            'appVersion' => $settings->appVersion,
            'notifyReleaseStages' => $settings->notifyReleaseStages,
            'filters' => $settings->filters,
            'ignoreBots' => $settings->ignoreBots,
            'metaData' => $settings->getMetadata(),
            'user' => $settings->user,
            'client' => $this->getService()->getClient(),
            'levels' => $settings->logTargetLevels,
            'categories' => $settings->logTargetCategories,
            'except' => $settings->logTargetExcept,
            'exceptCodes' => $settings->logTargetExceptCodes,
            'exceptPatterns' => $settings->logTargetExceptPatterns,
            'reportExceptions' => $settings->logTargetReportExceptions,
        ]);
    }

}
