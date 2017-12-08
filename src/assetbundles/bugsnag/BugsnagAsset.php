<?php
/**
 * Bugsnag plugin for Craft CMS 3.x
 *
 * Log Craft errors/exceptions to Bugsnag.
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

namespace superbig\bugsnag\assetbundles\Bugsnag;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Superbig
 * @package   Bugsnag
 * @since     2.0.0
 */
class BugsnagAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@superbig/bugsnag/assetbundles/bugsnag/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/Bugsnag.js',
        ];

        $this->css = [
            'css/Bugsnag.css',
        ];

        parent::init();
    }
}
