<?php

return [
    // Enable exception logging
    'enabled' => true,

    // Project Server API key
    'serverApiKey' => '',

    // Project Browser API key
    'browserApiKey' => '',

    // Bugsnag browser JavaScript CDN URL
    'browserCdnUrl' => 'https://d2wy8f7a9ursnm.cloudfront.net/v7.0.0/bugsnag.min.js',

    // Release stage
    'releaseStage' => 'production',

    // App version
    'appVersion' => '',

    // Release stages to log exceptions in
    'notifyReleaseStages' => ['production'],

    // Sensitive attributes to filter out, like 'password'
    'filters' => [],

    // Ignore exceptions and logs from known bots/crawlers
    'ignoreBots' => false,

    // Automatically leave breadcrumbs for Craft Commerce orders and transactions
    'commerceAutoBreadcrumbs' => false,

    // Automatically attach safe Craft Commerce order and transaction metadata
    'commerceAutoMetadata' => false,

    // Metadata to send with every request
    'metaData' => [],

    // Blacklist certain exception types like 404s
    'blacklist' => [],

    // Enable the Yii log target for Craft/Yii errors and warnings
    'logTargetEnabled' => true,

    // Yii log levels to send to Bugsnag
    'logTargetLevels' => ['error', 'warning'],

    // Yii log categories to include
    'logTargetCategories' => [],

    // Yii log categories to exclude
    'logTargetExcept' => [],

    // HTTP status codes to exclude from the Yii log target
    'logTargetExceptCodes' => [403, 404],

    // Message text patterns to exclude from the Yii log target
    'logTargetExceptPatterns' => [],

    // Whether the Yii log target should report Throwable log messages
    'logTargetReportExceptions' => false,
];
