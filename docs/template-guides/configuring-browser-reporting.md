# Configuring Browser Reporting

Browser reporting runs in the visitor’s browser and needs its own configuration. Use this helper with your browser logger setup, keeping server-side exception reporting separate.

If you register the plugin's front-end asset bundle, it loads the Bugsnag library from [`browserCdnUrl`](docs:get-started/configuration) and starts it for you. When you bundle the Bugsnag JavaScript client into your own build instead, use `craft.bugsnag.getBrowserConfig()` to pass the plugin's settings to it, so the browser reports to the same project and release stage as your server.

## Getting the Browser Configuration

`craft.bugsnag.getBrowserConfig(asJson)` returns the configuration for the Bugsnag browser client, built from your plugin settings. It contains `apiKey` (your `browserApiKey`), and, when they're set, `releaseStage`, `enabledReleaseStages` (from `notifyReleaseStages`), `metadata` and `user`.

`asJson` defaults to `true`, which returns the configuration as a JSON object ready to print inside a `<script>` tag. Pass `false` to get a Twig hash instead, which is useful when you want to read or change a value before you output it.

Place the call in the template that loads your JavaScript, before the script that starts Bugsnag. Any `craft.bugsnag.metadata()` calls need to come before it, as the configuration only includes metadata added up to that point.

```twig
{% do craft.bugsnag.metadata({ page: { section: entry.section.handle } }) %}

<script>
    window.bugsnagConfig = {{ craft.bugsnag.getBrowserConfig() }};
</script>
```

Your own script can then start the client with that configuration.

```js
import Bugsnag from '@bugsnag/js';

Bugsnag.start(window.bugsnagConfig);
```

To add a value the plugin doesn't manage, fetch the configuration as a hash, merge your value and encode it yourself.

```twig
{% set bugsnagConfig = craft.bugsnag.getBrowserConfig(false) | merge({
    appVersion: '2.4.0',
}) %}

<script>
    window.bugsnagConfig = {{ bugsnagConfig | json_encode | raw }};
</script>
```

The configuration is returned even when the plugin is disabled or has no `browserApiKey`, so `apiKey` may be empty. Check your settings in [Configuration](docs:get-started/configuration) if the browser client doesn't send reports.
