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
        'ignoreBots' => false,
        'commerceAutoBreadcrumbs' => false,
        'commerceAutoMetadata' => false,
        'blacklist' => [],
        'metaData' => [],
        'user' => true,
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
- `ignoreBots` - Whether to ignore exceptions and logs from known bots/crawlers.
- `commerceAutoBreadcrumbs` - Whether to automatically leave breadcrumbs for Craft Commerce orders and transactions when Commerce is installed.
- `commerceAutoMetadata` - Whether to automatically attach safe Craft Commerce order and transaction metadata to reports when Commerce is installed.
- `blacklist` - A collection of handlers for excluding exceptions sent to Bugsnag.
- `metaData` - Additional metadata sent to Bugsnag.
- `user` - User metadata sent to Bugsnag. Set to `true` for the default Craft user data, `false` to disable user data, an array to map fields, or a callable to build a custom payload.
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

### Customizing user metadata
By default, Bugsnag receives the logged-in Craft user’s `id`, `name`, and `email`. You can customize this from `config/bugsnag.php`:

```php
<?php

return [
    'user' => [
        'id' => 'id',
        'name' => 'fullName',
        'email' => 'email',
        'companyId' => 'companyId',
    ],
];
```

The array values can be Craft user attributes, custom field handles, or callables that receive the current user:

```php
<?php

return [
    'user' => function($user) {
        if (!$user) {
            return [];
        }

        return [
            'id' => $user->id,
            'email' => $user->email,
            'accountType' => $user->accountType,
        ];
    },
];
```

Set `'user' => false` if you do not want Bugsnag reports to include user data.

## Control Panel
You can also manage configuration settings through the Control Panel by visiting Settings → Bugsnag.
