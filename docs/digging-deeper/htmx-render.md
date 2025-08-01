# `Htmx::render()`

`Htmx::render()` is likely to be the main entry point for many of your frontend routes.

When you return `Htmx::render()` from a route it checks the current request to see if it's an HTMX AJAX request. 

If it is not - i.e., it's a fresh page load - it will render the full root application view and inline any defined [`Partial`s](partials.md). 

If it _is_ an AJAX request, it will only return the provided Partials to the frontend, which HTMX will then put into place for us.

This is inspired by Inertia's approach to handling route responses, where it returns a full page response for fresh requests and returns data for dynamic requests.

A bonus to this approach is that your application can save compute resources by doing less work for each AJAX request.

The simplest version of returning a response with `Htmx::render()` might look something like:

```php
use Elide\Htmx;

Route::get('about-us', fn() => Htmx::render('content.about-us'));
```


## `HtmxResponse`

The `Htmx::render()` method returns an `HtmxResponse`. This response object has a number of HTMX related methods which can be used to instruct HTMX on the frontend.

Read [`HtmxResponse`](htmx-response.md) for more details.

## Rendering a `View` or `Component` with additional props

The `Htmx::render()` function provides an easy way to pass additional properties to the Partial you are rendering.

There are a few ways this can be done.

```php
// With the view directly
Htmx::render(view('product.details', ['product' => $product]))
// ...or
Htmx::render(view('product.details')->with(['product' => $product]))

// For the view
Htmx::render(view('product.details'), props: ['product' => $product])
// ...or
Htmx::render('product.details', props: ['product' => $product])


use App\View\Components\Product\ProductDetails;

// With a component
Htmx::render(new ProductDetails($product))

// For a component (provided it can be instantiated with no props)
Htmx::render(new ProductDetails, props: ['product' => $product]);

// For a component class
Htmx::render(ProductDetails::class, props: ['product' => $product]);
```

## Including additional Partials to be rendered with the response

Sometimes you'll want to include or update other aspects of your frontend with your response. 

For example, perhaps someone has added a product to their shopping cart, and you'd like to send the cart summary widget to show the updated product count.

With Elide's Partials and HTMX on the frontend, this is easy.

```php
use App\View\Components\Product\ProductDetails;  
use App\View\Components\Store\CartSummary;  
use Elide\Htmx;  
  
Htmx::render(ProductDetails::class, props: ['product' => $product])  
    ->usingPartials(fn() => [  
        CartSummary::class,  
    ]);
```

Alternatively you can use the `Elide\Htmx` service to do this:

```php
Htmx::usingPartials(fn() => [  
    CartSummary::class,  
]);  
  
Htmx::render(ProductDetails::class, props: ['product' => $product]);
```

or

```php
if (cartWasUpdated()) {  
    Htmx::sendWithResponse(CartSummary::class);  
}  
  
Htmx::render(ProductDetails::class, props: ['product' => $product]);
```

## Scoping the response to specific partials

Elide can automatically detect which partial an HTMX AJAX request came from, and optionally scope down to return just that partial.

See [`Htmx::Response` - Scoping the response to specific partials](htmx-response.md#Scoping%20the%20response%20to%20specific%20partials)

