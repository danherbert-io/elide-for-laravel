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

## Scoping the response to specific partials

You can instruct Elide to return _only_ a specific partial, if an HTMX AJAX request has come from that partial, with the `scopeToRequestingPartial()` method.

```php
Htmx::render(...)->scopeToRequestingPartial();
```

You may enable scoping by calling the method, optionally passing `true` as the argument.

```php
Htmx::render(...)->scopeToRequestingPartial();
// or
Htmx::render(...)->scopeToRequestingPartial(shouldScope: true);
```

> [!TIP]
> If the requested partial is not available in the partials provided for the response, the response will include all provided partials as per default behaviour.

You may allow scoping for specific partials by providing an array of IDs:

```php
Htmx::render(...)->scopeToRequestingPartial(['user-profile-widget']]);
```

> [!TIP]
> If the requested partial is not included in the list of partials to scope to, the response will include all provided partials as per default behaviour.

You may disable scoping by passing `false`, or calling `doNotScopeToRequestingPartial()`.

```php
Htmx::render(...)->scopeToRequestingPartial(shouldScope: false);
// or
Htmx::render(...)->doNotScopeToRequestingPartial();
```

### An example

For example, we have a route which returns a transactions page for a bank account, and in this page is a transactions table (amongst other things):

```php
use App\View\Components\Page\BankAccountTransactionsPage;
use App\View\Components\Shared\MainNavigation;
use App\View\Components\Shared\UserProfileWidget;
use App\View\Components\Tables\AccountTransactionsTable;
use Elide\Htmx;

Route::get('transactions', function (BankAccount $bankAccount) {
    return Htmx::render(BankAccountTransactionsPage::class)
        ->usingPartials(fn() => [
            MainNavigation::class,
            UserProfileWidget::class,
            new AccountTransactionsTable($bankAccount),
        ]);
});
```

The transaction table is paginated, and has <kbd>Previous</kbd> and <kbd>Next</kbd> links which will fire an HTMX AJAX request.

Currently, any HTMX AJAX request made to this route will include all specified partials.

Adding `scopeToRequestingPartial()` to the route's response will instruct the route to only return the `AccountTransactionsTable` for the <kbd>Previous</kbd> and <kbd>Next</kbd> links.

## Filtering the returned partials in an HTMX response

Sometimes you may wish to return only specific partials to the frontend. There might be some UI logic or other conditions driving this choice.

In other scenarios returning partials to the frontend when they do not currently exist may trigger some HTMX `htmx:oobErrorNoTarget` errors in the browser console. In particular this may occur when using nested partials. The error usually has no side effects, though it is nice to clean it up.

You can filter the partials which Elide returns for an HTMX response by passing a `Collection` filter callback to the `filteringPartials()` method.

```php
return Htmx::render(...)->filteringPartials(  
    fn(string $renderedPartial, string $partialId) => $partialId === 'the-partial-id',  
);
```
