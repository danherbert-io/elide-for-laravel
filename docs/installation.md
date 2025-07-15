# Installation

Before installation make sure your project meets the [requirements](requirements.md).

### Add the package

You can install Elide with `composer`:

```bash
composer require danherbert-io/elide
```

### Create a root template

Elide needs a root template for rendering a full-page response. By default, this is `/resources/views/app.blade.php`.

It might look something like this:

```html
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

The view passed into `Htmx::render(...)` is the "main partial" to be rendered, and will be placed where `@htmxPartial('content')` is located in your template.

> [!TIP]
> A "partial" is an HTML fragment, usually provided by a view or Blade component. In Elide, the `Partial` class is a thin wrapper over these things and has been designed to streamline out-of-band swaps by handling most of that boilerplate for you.

You can also specify your view name directly for brevity:

```php
return Htmx::render('content.about-us');
```

> [!TIP]
> `Htmx::render()` also accepts `Illuminate\View\Component` class names or objects.

### That's it!

You now have a route which will return the full HTML markup for a fresh request, or only `view('content.about-us)` for
HTMX AJAX requests.