# Configuration

You can customise Bugsnag’s settings using a PHP configuration file. This is optional: each setting has a default, so you only need to include the values you want to change.

To override a setting, create `bugsnag.php` in your Craft project’s `/config` directory and return an array of setting names and values. For example, the following will ignore exceptions from known bots and crawlers:

```php
<?php

return [
    'ignoreBots' => true,
];
```

All other settings keep their defaults. Add any further settings you want to change to the same array. The options below explain the available settings and their defaults.

## Configuration Options

::: reference
### `enabled`

**Type:** `bool|string` · **Default:** `true`

Whether to enable the Bugsnag plugin.
:::

::: reference
### `serverApiKey`

**Type:** `string` · **Default:** `''`

The server API key for Bugsnag.
:::

::: reference
### `browserApiKey`

**Type:** `string` · **Default:** `''`

The browser API key for Bugsnag.
:::

::: reference
### `browserCdnUrl`

**Type:** `string` · **Default:** `'https://d2wy8f7a9ursnm.cloudfront.net/v7.0.0/bugsnag.min.js'`

The CDN URL used to load the Bugsnag browser JavaScript library.
:::

::: reference
### `releaseStage`

**Type:** `string` · **Default:** `'production'`

The release stage to send to Bugsnag.
:::

::: reference
### `appVersion`

**Type:** `string` · **Default:** `''`

The app version to send to Bugsnag.
:::

::: reference
### `notifyReleaseStages`

**Type:** `array` · **Default:** `['production']`

The release stages to send to Bugsnag.
:::

::: reference
### `filters`

**Type:** `array` · **Default:** `['password']`

Any data to filter from payloads sent to Bugsnag.
:::

::: reference
### `ignoreBots`

**Type:** `bool|string` · **Default:** `false`

Whether to ignore exceptions and logs from known bots/crawlers.
:::

::: reference
### `commerceAutoBreadcrumbs`

**Type:** `bool|string` · **Default:** `false`

Whether to automatically leave breadcrumbs for Craft Commerce orders and transactions when Commerce is installed.
:::

::: reference
### `commerceAutoMetadata`

**Type:** `bool|string` · **Default:** `false`

Whether to automatically attach safe Craft Commerce order and transaction metadata to reports when Commerce is installed.
:::

::: reference
### `blacklist`

**Type:** `array` · **Default:** `[]`

A collection of handlers for excluding exceptions sent to Bugsnag.
:::

::: reference
### `metaData`

**Type:** `array` · **Default:** `[]`

Additional metadata sent to Bugsnag.
:::

::: reference
### `user`

**Type:** `mixed` · **Default:** `true`

User metadata sent to Bugsnag. Set to `true` for the default Craft user data, `false` to disable user data, an array to map fields, or a callable to build a custom payload.
:::

::: reference
### `logTargetEnabled`

**Type:** `bool|string` · **Default:** `true`

Whether to register the Yii log target.
:::

::: reference
### `logTargetLevels`

**Type:** `array` · **Default:** `['error', 'warning']`

Yii log levels to send to Bugsnag. Defaults to `error` and `warning`.
:::

::: reference
### `logTargetCategories`

**Type:** `array` · **Default:** `[]`

Yii log categories to include. Leave empty to include all categories.
:::

::: reference
### `logTargetExcept`

**Type:** `array` · **Default:** `[]`

Yii log categories to exclude.
:::

::: reference
### `logTargetExceptCodes`

**Type:** `array` · **Default:** `[403, 404]`

HTTP status codes to exclude from the Yii log target.
:::

::: reference
### `logTargetExceptPatterns`

**Type:** `array` · **Default:** `[]`

Message text patterns to exclude from the Yii log target.
:::

::: reference
### `logTargetReportExceptions`

**Type:** `bool` · **Default:** `false`

Whether the Yii log target should also report logged `Throwable` messages. This is disabled by default to avoid duplicating the plugin’s exception-handler reports.
:::


### Blacklisting Exceptions
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

### Customizing User Metadata
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
