<?php

return [
    // Enable exception logging
    'enabled' => true,

    // Project Server API key
    'serverApiKey' => '',

    // Project Browser API key
    'browserApiKey' => '',

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
