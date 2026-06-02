# Configuration
Create a `bugsnag.php` file under your `/config` directory with the following options available to you. You can also use multi-environment options to change these per environment.

The below shows the defaults already used by Bugsnag, so you don't need to add these options unless you want to modify the values.

```php
<?php

return [
    '*' => [
        'enabled' => true,
        'serverApiKey' => '',
        'browserApiKey' => '',
        'browserCdnUrl' => 'https://d2wy8f7a9ursnm.cloudfront.net/v7.0.0/bugsnag.min.js',
        'releaseStage' => 'production',
        'appVersion' => '',
        'notifyReleaseStages' => ['production'],
        'filters' => ['password'],
        'blacklist' => [],
        'metaData' => [],
        'logTargetEnabled' => true,
        'logTargetLevels' => ['error', 'warning'],
        'logTargetCategories' => [],
        'logTargetExcept' => [],
        'logTargetExceptCodes' => [403, 404],
        'logTargetExceptPatterns' => [],
        'logTargetReportExceptions' => false,
    ],
];
```

## Configuration options
- `enabled` - Whether to enable the Bugsnag plugin.
- `serverApiKey` - The server API key for Bugsnag.
- `browserApiKey` - The browser API key for Bugsnag.
- `browserCdnUrl` - The CDN URL used to load the Bugsnag browser JavaScript library.
- `releaseStage` - The release stage to send to Bugsnag.
- `appVersion` - The app version to send to Bugsnag.
- `notifyReleaseStages` - The release stages to send to Bugsnag.
- `filters` - Any data to filter from payloads sent to Bugsnag.
- `blacklist` - A collection of handlers for excluding exceptions sent to Bugsnag.
- `metaData` - Additional metadata sent to Bugsnag.
- `logTargetEnabled` - Whether to register the Yii log target.
- `logTargetLevels` - Yii log levels to send to Bugsnag. Defaults to `error` and `warning`.
- `logTargetCategories` - Yii log categories to include. Leave empty to include all categories.
- `logTargetExcept` - Yii log categories to exclude.
- `logTargetExceptCodes` - HTTP status codes to exclude from the Yii log target.
- `logTargetExceptPatterns` - Message text patterns to exclude from the Yii log target.
- `logTargetReportExceptions` - Whether the Yii log target should also report logged `Throwable` messages. This is disabled by default to avoid duplicating the plugin’s exception-handler reports.

### Blacklisting exceptions
If you want to ignore a certain type of exception, like a 404-error: 

```php
<?php

use yii\web\NotFoundHttpException;

return [
    'blacklist' => [
        [
            'label' => '404 errors etc',
            'class' => function($exception) {
                if ($exception instanceof NotFoundHttpException && $exception->statusCode === 404) {
                    return false;
                }

                return true;
            },
        ],
    ],  
];
```

## Control Panel
You can also manage configuration settings through the Control Panel by visiting Settings → Bugsnag.
