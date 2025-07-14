# `Htmx::render()`

`Htmx::render()` is likely to be the main entry point for many of your rendered frontend routes.

When you return `Htmx::render()` from a route, it checks the current request to see if it's a HTMX AJAX request. 

If it is not - i.e., it's a fresh page load - it will render the full application view. If it _is_ an AJAX request, it will only return the provided [`Partial`s](partials.md) to the frontend, which HTMX will then put into place.

This is inspired by Inertia's approach to handling route responses.

A bonus to this approach is that your application can save compute resources by doing less work for each request.

The simplest version of this might look something like:

```php
Route::get('about-us', fn() => Htmx::render('page.about-us'));
```

