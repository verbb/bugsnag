# Bugsnag plugin for Craft CMS 3.x

Log Craft errors/exceptions to Bugsnag.

![Screenshot](resources/icon.png)

## Requirements

This plugin requires Craft CMS 3.0.0-RC1 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require superbig/craft3-bugsnag

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Bugsnag.

## Bugsnag Overview

Bugsnag's cross platform error monitoring automatically detects crashes in your applications, letting you ship with confidence.

## Configuring Bugsnag

1. Copy the config.php configuration file into your `craft/config` folder as **bugsnag.php**.
2. Update `serverApiKey` with a API key from your Bugsnag project.
3. (Optionally) Set the `releaseStage` configuration setting to something. Defaults to `production`.

If you want to be able to capture early initialization errors, you need to add this plugin to your project's bootstrap configuration. To do this, in `config/app.php`, add the following:

'bootstrap' => [
    '\superbig\bugsnag\Bootstrap',
]

### Blacklisting exceptions

If you want to ignore a certain type of exception, like a 404-error, you can do it like this: 

```php
<?php
return [
  'blacklist' => [
          [
              'label' => '404 errors etc',
              'class' => function($exception) {
                  /**
                   * @var \yii\web\NotFoundHttpException $exception
                   */
                  if ($exception instanceof \yii\web\NotFoundHttpException && $exception->statusCode === 404) {
                      return false;
                  }
  
                  return true;
              },
          ],
      ],  
];
```

## Using Bugsnag

It will automatically log most exceptions/errors. If you want to log a exceptions/error from an custom plugin, you may use the service methods:

- For exceptions: `Bugsnag::$plugin->bugsnagService->handleException($exception);`

## Using Bugsnag on the frontend

You can log JavaScript errors on your site, by including the following in your Twig templates:

```twig
{% do view.registerAssetBundle('superbig\\bugsnag\\assetbundles\\frontend\\FrontEndAsset') %}
```

This currently uses v7.0.0 of the Bugsnag library.

You also need to set the `browserApiKey` setting.

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

Brought to you by [Superbig](https://superbig.co)
