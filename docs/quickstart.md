# Quickstart

Adding Elide to your Laravel project only takes a few steps.

1. Install Elide into your project
2. Create a root view for full page renders
3. Setup routes to return your components with a dynamic HTMX response

### 1. Install Elide

```bash
composer require danherbert-io/elide
```

### 2. Create a root template

Elide needs a root template to use for full page renders. By default Elide looks for `resources/views/app.blade.php`.

The root template is comparable to `resources/views/app.blade.php` when you've scaffolded a Laravel project with the Inertia + React/Vue starter kits.

It should contain all the main structure and "chrome" which surrounds the content.

A barebone example:

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

    <main>
    
        @htmxPartial('content')
        
    </main>
    
</body>
</html>
```

### 3. Return a HTMX page response with a `View` or `Component`

The easiest way to respond to a basic request is to pass a `View` or `Component` to `Htmx::render()`:

```php
Route::get('about-us', fn() => Htmx::render(view('content.about-us')));
```

In this example, when someone visits `/about-us` from a fresh page view, the full root template will be returned, and the rendered `view('content.about-us')` will be injected where `@htmxPartial('content')` is. 

Then, if someone has followed a HTMX boosted link to `/about-us`, Elide will detect this and only return the rendered `view('content.about-us')` - HTMX will automatically place this in the right location for you.

**That's it!**


### Further reading

* [Requirements](./requirements.md)
* [Installation](./installation.md)
* [Digging deeper](./digging-deeper.md)