<p align="center"><img src="/docs/art/elide-logo.svg" alt="Elide package logo" style="max-width: 300px"></p>

<p align="center">
<img src="https://github.com/danherbert-io/elide-for-laravel/workflows/tests/badge.svg">
</p>

## Introduction

Elide is a small package for use with [Laravel](https://laravel.com/) and [HTMX](https://htmx.org/). It aims to streamline the gap between backend and frontend by making it easy to return full page responses, or just partials (i.e., components). It also provides handy methods of checking HTMX properties of requests and responses.

Strongly inspired by [Inertia](https://github.com/inertiajs/inertia-laravel).

```php
use Elide\Htmx;
use App\Models\Product;
use App\View\Components\Content\ProductDetails;
use App\View\Components\Ui\ToastNotification;

class ViewProductController {
    public function __invoke(Product $product) {
        if (wasAddedToCart($product)) {
            Htmx::sendWithResponse(new ToastNotification('Added to cart!'));
        }
        
        return Htmx::render(new ProductDetails($product));
    }
}
```

> [!IMPORTANT]
> We're still writing Elide's documentation. For a practical example/reference you can check out the demonstration site: https://github.com/danherbert-io/elide-demo-site


Elide is open-source software licensed under the MIT licence.

## Documentation

> [!IMPORTANT]
> Elide leverages HTMX's swap attributes - you should have an understanding of how HTMX works before using Elide.
> [HTMX's documentation](https://htmx.org/docs/) covers pretty much everything, and the [HTMX examples](https://htmx.org/examples/) provide a lot of practical examples of how to use HTMX in the frontend to talk to the backend.

* [Quickstart](./docs/quickstart.md)


* [Requirements](./docs/requirements.md)
* [Installation](./docs/installation.md)
* [Digging deeper](./docs/digging-deeper.md)


* [Guides/Recipes](./docs/guides-recipes.md)

## Other resources

* Maurizio's [Laravel HTMX](https://github.com/mauricius/laravel-htmx) package
* [HTMX](https://htmx.org/)
* [ThePrimeagen's intro to HTMX](https://www.youtube.com/watch?v=x7v6SNIgJpE) (it's for Golang, but still a great introduction to HTMX)