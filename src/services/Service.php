<?php
namespace verbb\bugsnag\services;

use verbb\bugsnag\Bugsnag;
use verbb\bugsnag\models\Settings;

use Craft;
use craft\base\Component;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\Client;

use DateTimeInterface;

class Service extends Component
{
    // Properties
    // =========================================================================

    public array $metadata = [];

    private ?Settings $settings = null;
    private ?Client $bugsnag = null;


    // Public Methods
    // =========================================================================

    public function init(): void
    {
        $this->settings = Bugsnag::$plugin->getSettings();

        if ($this->isEnabled()) {
            $this->bugsnag = Client::make($this->settings->getServerApiKey());

            $this->bugsnag->setReleaseStage($this->settings->getReleaseStage());
            $this->bugsnag->setAppVersion($this->settings->appVersion);
            $this->bugsnag->setNotifyReleaseStages($this->settings->notifyReleaseStages);

            if (!empty($this->settings->filters)) {
                $this->bugsnag->setRedactedKeys($this->settings->filters);
            }

            $this->bugsnag->registerCallback(function($report) {
                $metadata = array_replace_recursive($this->settings->metaData, $this->metadata);

                if (!empty($metadata)) {
                    $report->setMetaData($metadata);
                }

                if ($user = Craft::$app->getUser()->getIdentity()) {
                    $report->setUser([
                        'id' => $user->id,
                        'name' => $user->getName(),
                        'email' => $user->email,
                    ]);
                }
            });
        }
    }

    public function breadcrumb(string $text = '', string $type = Breadcrumb::MANUAL_TYPE, array $metaData = []): bool
    {
        if (empty($text) || !$this->isEnabled() || !$this->bugsnag) {
            return false;
        }

        $this->bugsnag->leaveBreadcrumb($text, $type, $metaData);

        return true;
    }

    public function metadata(array $metadata = []): Service
    {
        $this->metadata = array_replace_recursive($this->metadata, $metadata);

        return $this;
    }

    public function handleCommerceOrder(mixed $order): void
    {
        if (!$this->_shouldCaptureCommerce()) {
            return;
        }

        $metadata = $this->_getCommerceOrderMetadata($order);

        if ($this->settings->getCommerceAutoMetadata()) {
            $this->metadata(['commerce' => ['order' => $metadata]]);
        }

        if ($this->settings->getCommerceAutoBreadcrumbs()) {
            $label = ($metadata['isCompleted'] ?? false) ? 'Commerce order saved' : 'Commerce cart saved';
            $this->breadcrumb($label, Breadcrumb::MANUAL_TYPE, $metadata);
        }
    }

    public function handleCommerceTransaction(mixed $transaction): void
    {
        if (!$this->_shouldCaptureCommerce()) {
            return;
        }

        $metadata = $this->_getCommerceTransactionMetadata($transaction);

        if ($this->settings->getCommerceAutoMetadata()) {
            $this->metadata(['commerce' => ['transaction' => $metadata]]);
        }

        if ($this->settings->getCommerceAutoBreadcrumbs()) {
            $this->breadcrumb('Commerce transaction saved', Breadcrumb::MANUAL_TYPE, $metadata);
        }
    }

    public function handleException($exception): void
    {
        if (!$this->isEnabled() || $this->settings->shouldIgnoreCurrentRequest()) {
            return;
        }

        $this->bugsnag->notifyException($exception);
    }

    public function getClient(): ?Client
    {
        return $this->bugsnag;
    }

    public function isEnabled(): bool
    {
        return $this->settings->getEnabled() && !empty($this->settings->getServerApiKey());
    }


    // Private Methods
    // =========================================================================

    private function _shouldCaptureCommerce(): bool
    {
        return $this->settings->getCommerceAutoBreadcrumbs() || $this->settings->getCommerceAutoMetadata();
    }

    private function _getCommerceOrderMetadata(mixed $order): array
    {
        return $this->_filterMetadata([
            'id' => $this->_readValue($order, 'id'),
            'number' => $this->_readValue($order, 'number'),
            'reference' => $this->_readValue($order, 'reference'),
            'customerId' => $this->_readValue($order, 'customerId'),
            'userId' => $this->_readValue($order, 'userId'),
            'orderStatusId' => $this->_readValue($order, 'orderStatusId'),
            'totalPrice' => $this->_readValue($order, 'totalPrice'),
            'totalPaid' => $this->_readValue($order, 'totalPaid'),
            'currency' => $this->_readValue($order, 'currency'),
            'isCompleted' => $this->_readValue($order, 'isCompleted'),
            'dateOrdered' => $this->_readValue($order, 'dateOrdered'),
        ]);
    }

    private function _getCommerceTransactionMetadata(mixed $transaction): array
    {
        return $this->_filterMetadata([
            'id' => $this->_readValue($transaction, 'id'),
            'orderId' => $this->_readValue($transaction, 'orderId'),
            'parentId' => $this->_readValue($transaction, 'parentId'),
            'gatewayId' => $this->_readValue($transaction, 'gatewayId'),
            'type' => $this->_readValue($transaction, 'type'),
            'status' => $this->_readValue($transaction, 'status'),
            'code' => $this->_readValue($transaction, 'code'),
            'paymentAmount' => $this->_readValue($transaction, 'paymentAmount'),
            'paymentCurrency' => $this->_readValue($transaction, 'paymentCurrency'),
        ]);
    }

    private function _readValue(mixed $object, string $name): mixed
    {
        if (!is_object($object)) {
            return null;
        }

        $getter = 'get' . ucfirst($name);

        if (method_exists($object, $getter)) {
            return $object->{$getter}();
        }

        if (method_exists($object, 'canGetProperty') && $object->canGetProperty($name)) {
            return $object->{$name};
        }

        if (property_exists($object, $name)) {
            return $object->{$name};
        }

        return null;
    }

    private function _filterMetadata(array $metadata): array
    {
        $filtered = [];

        foreach ($metadata as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $filtered[$key] = $this->_normalizeMetadataValue($value);
        }

        return $filtered;
    }

    private function _normalizeMetadataValue(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        if (is_scalar($value) || $value === null) {
            return $value;
        }

        if (is_array($value)) {
            return array_map(fn($item) => $this->_normalizeMetadataValue($item), $value);
        }

        if (method_exists($value, '__toString')) {
            return (string)$value;
        }

        return get_debug_type($value);
    }
}
