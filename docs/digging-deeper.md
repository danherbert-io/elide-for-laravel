# Digging Deeper


- [partials](./digging-deeper/partials.md)
- [htmx-render](./digging-deeper/htmx-render.md)
- [htmx-request](./digging-deeper/htmx-request.md)
- [htmx-response](./digging-deeper/htmx-response.md)






---


The steps provided in [the installation instructions](installation.md) are very basic. You'll usually want to be sending a bunch of different components to the frontend in a range of different scenarios. Such components might be: application navigation, toast notifications, a shopping cart with quantity indicator, user profile menu... Anything potentially requiring dynamic updates.

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

In our `app.blade.php` file, we might now add a reference to this partial:

```bladehtml

<div class="flex justify-between items-center">
    @htmxPartial('application-navigation')
</div>

@htmxPartial('content')
```

> [!TIP]
> It is important to note that Elide handles rendering of views and components for you — you do not need to reference the component itself via `<x-application-navigation ...>`

> [!TIP]
> The name of the partial which we have used in our template (`application-navigation`) is automatically determined from the component's class name.

Now that our app template looks for this partial, we can instruct Elide to return this component with every HTMX AJAX request.

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