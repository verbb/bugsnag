# Usage
We recommend adding the following to your `config/app.php` file. This will capture early initialization errors.

```php
<?php

return [
    'modules' => [
        // ...
    ],
    'bootstrap' => [
        // ...
        '\verbb\bugsnag\Bootstrap',
    ],
];
```


## Using Bugsnag
The Bugsnag plugin will automatically log most exceptions/errors. If you want to log an exceptions/error from a custom plugin, you may use the service methods:

```php
use verbb\bugsnag\Bugsnag;

Bugsnag::$plugin->getService()->handleException($exception);
```

## Using Bugsnag on the frontend
You can log JavaScript errors on your site, by including the following in your Twig templates:

```twig
{% do view.registerAssetBundle('verbb\\bugsnag\\assetbundles\\frontend\\FrontEndAsset') %}
```

This uses the `browserCdnUrl` setting, which defaults to v7.0.0 of the Bugsnag library. You also need to set the `browserApiKey` setting.

If you'd rather include the Bugsnag client in your build and initialize it yourself, there is a helper method to get the browser config based on your plugin settings:

```twig
<script>
    const bugsnagConfig = { ...{{ craft.bugsnag.getBrowserConfig(true) }} }
</script>
```

The method takes one parameter, that toggles if it should return JSON or not.

### Adding metadata from templates
If you want to send custom metadata with your request, you may do something like this:

```twig
{% do craft.bugsnag.metadata({ orderId: cart.id }) %}
```

Note that you have to call these methods before you include the JS bundle.

### Throwing an exception from templates
You can trigger an exception from your templates.

```twig
{% do craft.bugsnag.handleException('Something went terribly wrong.') %}
```

## Using the Yii log target
The plugin automatically registers a Yii log target that sends Craft/Yii `error` and `warning` log messages to Bugsnag. This includes calls such as `Craft::error()` and `Craft::warning()`.

Logged `Throwable` messages are not reported by the log target by default, because the plugin already reports exceptions through Craft’s error handler. If you want the log target to report logged exceptions too, enable the `logTargetReportExceptions` setting.

### Advanced early registration
For the earliest possible log capture, you can register the log target directly in `config/app.php`. This adds the target to Yii’s log component before Craft plugins and modules are loaded.

```php
<?php

use craft\helpers\App;
use verbb\bugsnag\log\BugsnagTarget;

return [
    'components' => [
        'log' => [
            'targets' => [
                'bugsnag' => function() {
                    return Craft::createObject([
                        'class' => BugsnagTarget::class,
                        'enabled' => App::env('CRAFT_ENVIRONMENT') !== 'dev',
                        'serverApiKey' => App::env('BUGSNAG_SERVER_API_KEY'),
                        'releaseStage' => App::env('CRAFT_ENVIRONMENT') ?: 'production',
                        'levels' => ['error', 'warning'],
                        'exceptCodes' => [403, 404],
                    ]);
                },
            ],
        ],
    ],
];
```