<p align="center"><img src="/docs/art/elide-logo.svg" alt="Elide package logo" style="max-width: 300px"></p>

<p align="center">
[![tests](https://github.com/danherbert-io/elide-for-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/danherbert-io/elide-for-laravel/actions/workflows/tests.yml)
</p>

## Intro

Elide is a small package for use with [Laravel](https://laravel.com/) and [HTMX](https://htmx.org/). It aims to streamline the gap between backend and frontend by making it easier to return full page responses, or just necessary partials (i.e., components). It also provides handy methods of checking HTMX properties of requests and responses.

Strongly inspired by [Inertia](https://github.com/inertiajs/inertia-laravel).

> [!IMPORTANT]
> Elide is in early development. Perhaps best not to use in production just yet.

* [Requirements](./docs/requirements.md)
* [Installation](./docs/installation.md)
* [Digging deeper](./docs/digging-deeper.md)

## Quick start

### Install Elide

```bash
composer require danherbert-io/elide
```

### Set up a root template

`resources/views/app.blade.php`

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

### Return a HTMX response

```php
Route::get('about-us', fn() => Htmx::render(view('content.about-us')));
```
(`view('content.about-us')` will be used for `@htmxPartial('content')`)

## Other Laravel+HTMX resources

* Maurizio's [Laravel HTMX](https://github.com/mauricius/laravel-htmx) package