<?php
namespace superbig\bugsnag;

use Craft;
use craft\base\Plugin;

use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    /**
     * Installs our components during the bootstrap process to get us loaded 
     * sooner in case something crashes.
     *
     * @param \yii\base\Application $app
     */
    public function bootstrap($app)
    {
        $app->getPlugins()->getPlugin('bugsnag');
    }
}
