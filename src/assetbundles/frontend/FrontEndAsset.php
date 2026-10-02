<?php
namespace verbb\bugsnag\assetbundles\frontend;

use verbb\bugsnag\web\assets\frontend\FrontEndAsset as NewFrontEndAsset;

use Craft;

/**
 * @deprecated Use {@see NewFrontEndAsset} instead.
 */
class FrontEndAsset extends NewFrontEndAsset
{
    // Public Methods
    // =========================================================================

    public function init(): void
    {
        Craft::$app->getDeprecator()->log(
            self::class,
            '`' . self::class . '` has been deprecated. Use `' . NewFrontEndAsset::class . '` instead.',
        );

        parent::init();
    }
}
