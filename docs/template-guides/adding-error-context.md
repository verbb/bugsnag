# Adding Error Context

Add context before the operation you want to diagnose. Metadata describes the request, while breadcrumbs record the steps leading to an error. Include relevant identifiers without placing passwords or private payloads in your reports.

For example, a checkout template might record the cart it's showing and leave a breadcrumb as the visitor reaches the payment step. If an error is reported later in the same request, Bugsnag shows both alongside it.

```twig
{% do craft.bugsnag.metadata({
    checkout: {
        cartId: cart.id,
        step: 'payment',
    },
}) %}

{% do craft.bugsnag.breadcrumb('Viewed payment step', 'navigation', { cartId: cart.id }) %}
```

Place these calls near the top of the template, before any code that might fail. They only affect the current request, so a later page load starts without them.

## Adding Metadata to Reports

`craft.bugsnag.metadata(data)` adds custom metadata to any report sent during the current request. Pass a hash of values, usually grouped under a tab name such as `checkout`, which Bugsnag shows as its own tab on the report. `data` defaults to an empty hash.

Each call is merged recursively with earlier calls in the same request, and with the [`metaData`](docs:get-started/configuration) setting, so you can add values from several templates without replacing what's already there. The call returns the plugin's service rather than output, so use it with `{% do %}`.

```twig
{% do craft.bugsnag.metadata({ page: { section: entry.section.handle } }) %}
{% do craft.bugsnag.metadata({ page: { entryId: entry.id } }) %}

{# Reports from this request include `page.section` and `page.entryId` #}
```

The same metadata is included in the browser configuration, as long as you call `metadata()` before registering the front-end asset bundle or calling `craft.bugsnag.getBrowserConfig()`. See [Configuring Browser Reporting](docs:template-guides/configuring-browser-reporting).

## Leaving Breadcrumbs

`craft.bugsnag.breadcrumb(text, type, metaData)` leaves a breadcrumb for the current request. Bugsnag lists breadcrumbs in order on a report, so they help you see what happened before an error.

`text` is the breadcrumb's name and is required. `type` defaults to `manual` and can be any Bugsnag breadcrumb type: `navigation`, `request`, `process`, `log`, `user`, `state`, `error` or `manual`. Any other value is recorded as `manual`. `metaData` is an optional hash of extra values shown with the breadcrumb.

The call returns `true` when the breadcrumb is recorded, and `false` when `text` is empty or server reporting is disabled or has no `serverApiKey`. Use it with `{% do %}` unless you want to check the result.

```twig
{% do craft.bugsnag.breadcrumb('Applied discount code', 'user', { code: cart.couponCode }) %}
```

Server-side breadcrumbs are attached to PHP reports. To leave breadcrumbs from JavaScript, use the browser client's own `Bugsnag.leaveBreadcrumb()`, described in [Usage](docs:feature-tour/usage).

## Reporting a Handled Exception

`craft.bugsnag.handleException(exception)` sends an exception to Bugsnag straight away as a handled error, without interrupting the page. Nothing is output.

`exception` must be a PHP exception object, such as one passed to your template by a module or plugin. A plain string isn't accepted, and Bugsnag's client throws an error if you pass one. Nothing is sent when server reporting is disabled or has no `serverApiKey`, or when the request comes from a bot and [`ignoreBots`](docs:get-started/configuration) is enabled.

```twig
{# `importError` is an exception object passed to the template by a custom module #}
{% if importError is defined and importError %}
    {% do craft.bugsnag.handleException(importError) %}
{% endif %}
```

Most exceptions are already reported automatically through Craft's error handler, so you only need this call for an error that your own code has caught and handled.
