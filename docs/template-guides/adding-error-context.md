# Adding Error Context

Add context before the operation you want to diagnose. Metadata describes the request, while breadcrumbs record the steps leading to an error. Include relevant identifiers without placing passwords or private payloads in your reports.

```twig
{% do craft.bugsnag.metadata({page: {section: 'catalogue'}}) %}
```

## Calls Used in This Task

### `craft.bugsnag.metadata(data)`
Set metadata for exceptions.

### `craft.bugsnag.breadcrumb(text, type, metaData)`
Leave a breadcrumb for the current request.

### `craft.bugsnag.handleException(exception)`
Send an exception to Bugsnag.
