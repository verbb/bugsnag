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

    // Metadata to send with every request
    'metaData' => [],

    // Blacklist certain exception types like 404s
    'blacklist' => [],
];
