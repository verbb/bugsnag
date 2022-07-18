<?php
namespace verbb\bugsnag;

use yii\base\BootstrapInterface;
use yii\base\Application;

class Bootstrap implements BootstrapInterface
{
    /**
     * Installs our components during the bootstrap process to get us loaded
     * sooner in case something crashes.
     *
     * @param Application $app
     */
    public function bootstrap($app): void
    {
        $app->getPlugins()->getPlugin('bugsnag');
    }
}
