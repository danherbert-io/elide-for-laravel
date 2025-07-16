# `HtmxResponse`

The `Elide\Http\HtmxResponse` class is a response class which makes a decision about how to respond to requests, based on whether the request is an an HTMX AJAX request or not.

Most often you will retrieve an `HtmxResponse` from the `Htmx::render()` method, though you may instantiate one yourself.

```php
use Elide\Http\HtmxResponse;

$response = new HtmxResponse();
```


## Sending a full page response or a Partials only response to the frontend

When `HtmxResponse`  is instantiated with a `View` or `Component`, it will make a decision about what it will send to the frontend based on the current request.

```php
Route::get('about-us', fn() => new HtmxResponse(view('content.about-us')))
```

In this example, if the request is a fresh non-AJAX request, a full page response generated from the root application view will be returned.

If the request is an AJAX request, only the rendered `Partial` result of `view('content.about-us')` will be returned.

## Sending an instructions-only response for HTMX

Sometimes you may not wish to send any actual content back to the frontend, but you may wish to send instructions to HTMX to react to.

For example, you may instruct HTMX to refresh the browser with the following:

```php
return (new \Elide\Http\HtmxResponse)->refresh();
```

... or specify a location for HTMX to navigate to:

```php
return (new \Elide\Http\HtmxResponse)->location($url);
```


### Available HTMX instructions

The following methods can be chained.

#### `title()`

You may set a new frontend page title:

```php
return (new \Elide\Http\HtmxResponse)->title('The new title');
```

#### `location()`

You may instruct HTMX to navigation to a new location:

```php
return (new \Elide\Http\HtmxResponse)->location($url);
```

See https://htmx.org/headers/hx-location/
#### `pushUrl()`

You may instruct HTMX to push a URL:

```php
return (new \Elide\Http\HtmxResponse)->pushUrl($url);
```

See https://htmx.org/headers/hx-push-url/
#### `redirect()`

You may instruct HTMX to perform a full redirect to a URL:

```php
return (new \Elide\Http\HtmxResponse)->redirect($url);
```

See https://htmx.org/headers/hx-redirect/
#### `refresh()`

You may instruct HTMX to perform browser refresh:

```php
return (new \Elide\Http\HtmxResponse)->refresh();
```

See https://htmx.org/headers/hx-refresh/
#### `replaceUrl()`

You may instruct HTMX to replace the URL with the provided URL:

```php
return (new \Elide\Http\HtmxResponse)->replaceUrl($url);
```

See https://htmx.org/headers/hx-replace-url/
#### `reswap()`

You may instruct HTMX to use a new swap:

```php
return (new \Elide\Http\HtmxResponse)->reswap($newSwap);
```

See https://htmx.org/docs/#response-headers

#### `retarget()`

You may instruct HTMX to use a new target:

```php
return (new \Elide\Http\HtmxResponse)->retarget($newTarget);
```

See https://htmx.org/docs/#response-headers
#### `reselect()`

You may instruct HTMX to reselect:

```php
return (new \Elide\Http\HtmxResponse)->reselect($cssSelector);
```

See https://htmx.org/docs/#response-headers
#### `trigger()`

You may instruct HTMX to trigger a JS event:

```php
return (new \Elide\Http\HtmxResponse)->reselect($event);
```

See https://htmx.org/headers/hx-trigger/