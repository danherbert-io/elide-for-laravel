<p align="center"><img src="/art/elide-logo.svg" alt="Elide package logo" style="max-width: 300px"></p>

## Intro

Elide is a small package for use with [Laravel](https://laravel.com/) and [HTMX](https://htmx.org/). It aims to
streamline the gap between backend and frontend by making it easier to return full page responses, or just necessary
partials (i.e., components).

Strongly inspired by [Inertia](https://github.com/inertiajs/inertia-laravel).

> [!IMPORTANT]
> Elide is in early development. Perhaps best not to use in production just yet.

## Installation

### Add the package

You can install Elide via `composer`:

```bash
composer require danherbert-io/elide
```

### Create a root template

Elide needs a root template for rendering a full-page response. By default, this is `/resources/views/app.blade.php`.

It might look something like this:

```bladehtml
<!doctype html>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{$title ?? '??'}} / {{config('app.name')}}</title>
    @vite(['resources/css/app.css'])
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.6/dist/htmx.min.js"
            integrity="sha384-Akqfrbj/HpNVo8k11SXBb6TlBWmXXlYQrCSqEWmyKJe+hDm3Z/B2WVG4smwBkRVm"
            crossorigin="anonymous"></script>
</head>
<body>
@htmxPartial('content')
</body>
</html>
```

### Setup a route

Next, we need to set up a route which can handle full page loads or partial HTMX responses.

```php
use Illuminate\Support\Facades\Route;
use Elide\Htmx;

Route::get('about-us', function() {
    return Htmx::render(view('content.about-us'));
});
```

The view passed into `Htmx::render(...)` is the "main partial" to be rendered, and will be placed where
`@htmxPartial('content')` is located in your template.

> [!TIP] A "partial" is an HTML fragment, usually provided by a view or Blade component. In Elide, the `Partial` class
> is a thin wrapper over these things designed to streamline out-of-band swaps. More on this later...

You can also specify your view name directly for brevity:

```php
return Htmx::render('content.about-us');
```

> [!TIP]
> `Htmx::render()` also accepts `Illuminate\View\Component` class names or objects.

### That's it!

You now have a route which will return the full HTML markup for a fresh request, or only `view('content.about-us)` for
HTMX AJAX requests.

## Digging deeper...

The above is a basic setup, and not really that useful. You'll usually want to be sending a bunch of different
components back to the frontend, such as application navigation, toast notifications, a shopping cart with quantity
indicator, user profile menu... Anything potentially requiring dynamic updates.

### Specifying additional partials to be sent with every `Htmx::render()` response

Say we have the following `ApplicationNavigation` Blade component:

```php
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ApplicationNavigation extends Component
{
    public function render(): View
    {
        return view('components.application-navigation');
    }
}
```

In our `app.blade.php` file, we now add a reference to this partial:

```bladehtml

<div class="flex justify-between items-center">
    @htmxPartial('application-navigation')
</div>

@htmxPartial('content')
```

> [!TIP]
> It is important to note that Elide handles rendering of the component for you — you do not need to reference the
> component itself via `<x-application-navigation ...>`

> [!TIP]
> The name of the partial (`application-navigation`) is automatically determined from the component's class name.

We can instruct Elide to return this component with every HTMX AJAX request.

One way of doing this would be to add it to your app's service provider:

```php
use App\View\Components\ApplicationNavigation;
use Elide\Htmx;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Htmx::usingPartials(fn() => [
            ApplicationNavigation::class,
            // Other Blade components can be registered here.
            // You can also instantiate your components if needed, eg:
            //     new ApplicationNavigation(),
        ]);
    }
}
```

Another way to approach this would be to create middleware and call `Htmx::usingPartials(...)` from there. This way you
could specify different components to be returned for different route groups.

```php
use App\View\Components\ApplicationNavigation;
use Closure;
use Elide\Htmx;
use Illuminate\Http\Request;

class TestMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        Htmx::usingPartials(fn() => [
            ApplicationNavigation::class,
        ]);

        return $next($request);
    }
}
```

### Adding one-off partials to a response

Sometimes you might want to include an additional partial in your response. For example, perhaps your user has
performed an action on a product and you want to include a status message.

Say we have this `StatusMessage` component:

```php
class StatusMessage extends Component
{
    public function __construct(
        public string $message,
    ) {}

    public function render()
    {
        return view('components.status-message');
    }
}
```

You can include that in a response like this:

```php
Route::post('do-thing-with-product', function(Product $product) {
    // ... do the thing to the product
    
    Htmx::sendWithResponse(new StatusMessage('Product has been updated'));

    return Htmx::render(view('content.about-us', [
        'product' => $product,
    ]));
});
```

Alternatively, you can also do it like this:

```php
Route::post('do-thing-with-product', function(Product $product) {
    // ... do the thing to the product

    return Htmx::render(view('content.about-us', [
        'product' => $product,
    ]))->usingPartials(fn() => [
        new StatusMessage('Product has been updated')
    ]);
});
```


## Other links and packages
