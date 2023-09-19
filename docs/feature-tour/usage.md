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

This currently uses v7.0.0 of the Bugsnag library. You also need to set the `browserApiKey` setting.

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