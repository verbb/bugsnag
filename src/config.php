<?php
/**
 * Bugsnag plugin for Craft CMS 3.x
 *
 * Log Craft errors/exceptions to Bugsnag.
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

/**
 * Bugsnag config.php
 *
 * This file exists only as a template for the Bugsnag settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'bugsnag.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [
    // Enable exception logging
    'enabled'             => true,

    // Project Server API key
    'serverApiKey'        => '',

    // Project Browser API key
    'browserApiKey'        => '',

    // Release stage
    'releaseStage'        => 'production',

    // App version
    'appVersion'          => '',

    // Release stages to log exceptions in
    'notifyReleaseStages' => [ 'production' ],

    // Sensitive attributes to filter out, like 'password'
    'filters'             => [],

    // Metadata to send with every request
    'metaData'            => [],

    // Blacklist certain exception types like 404s
    'blacklist'            => [],
];
