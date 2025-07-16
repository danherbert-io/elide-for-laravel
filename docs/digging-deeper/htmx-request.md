# `HtmxRequest`

The `Elide\Http\HtmxRequest` class allows you to easily inspect HTMX related properties of the current request.

You can resolve it from the application container the same as you would Laravel's own `Illuminate\Http\Request` class.

```php
use Elide\Http\HtmxRequest;

Route::get('about-us', function(HtmxRequest $request) {
    // ...
})

// or
$request = app(HtmxRequest::class);
```

## Available HTMX inspections


### `isHtmxRequest()`

Check if the request was an HTMX AJAX request.

```php
use Elide\Http\HtmxRequest;

$htmxRequest = app(HtmxRequest::class)->isHtmxRequest();
```


### `isBoosted()`

Check if the request was a boosted HTMX AJAX request.

```php
use Elide\Http\HtmxRequest;

$boosted = app(HtmxRequest::class)->isBoosted();
```

### `currentUrl()`

The current frontend URL from which the HTMX request was made.

```php
use Elide\Http\HtmxRequest;

$url = app(HtmxRequest::class)->currentUrl();
```

### `isHistoryRestoreRequest()`

Check if the request was an HTMX AJAX request.

```php
use Elide\Http\HtmxRequest;

$historyRestored = app(HtmxRequest::class)->isHistoryRestoreRequest();
```

### `isPrompt()`

Check if the request was an HTMX AJAX prompt.

```php
use Elide\Http\HtmxRequest;

$prompted = app(HtmxRequest::class)->isPrompt();
```

### `target()`

Check if the HTML target element of the HTMX request.

```php
use Elide\Http\HtmxRequest;

$target = app(HtmxRequest::class)->target();
```

### `triggerName()`

Check if the trigger name of the HTMX request.

```php
use Elide\Http\HtmxRequest;

$triggerName = app(HtmxRequest::class)->triggerName();
```

### `trigger()`

Check if the trigger of the HTMX request.

```php
use Elide\Http\HtmxRequest;

$trigger = app(HtmxRequest::class)->trigger();
```

### `inspect()`

Returns an array of all the HTMX related values.

```php
use Elide\Http\HtmxRequest;

$htmxInfo = app(HtmxRequest::class)->inspect();
```

```text
[
  "isBoosted" => true,
  "currentUrl" => "https://...",
  "isHistoryStoreRequest" => false,
  "isPrompt" => false,
  "isHtmxRequest" => true,
  "target" => null,
  "triggerName" => null,
  "trigger" => null,
]
```