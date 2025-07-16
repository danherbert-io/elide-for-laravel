# Simple content site with navigation menu

> [!TIP]
> Suggest reading:
> - [HTMX's `hx-boost` attribute](https://htmx.org/attributes/hx-boost/)

## 1. Create a fresh root template

Elide needs a root template with which to render full page responses. Let's create that.

Things we want to include:

- A simple structure
- HTMX via CDN (you can `npm i` HTMX of course - that is not detailed here)
- A place for content to be rendered
- A place to put the site navigation

`resources/views/app.blade.php`

```html
<!doctype html>
<html lang="en">
<head>
    <title>{{$title ?? 'no title set'}}</title>
    <script src="https://cdn.jsdelivr.net/npm/htmx.org@2.0.6/dist/htmx.min.js"
            integrity="sha384-Akqfrbj/HpNVo8k11SXBb6TlBWmXXlYQrCSqEWmyKJe+hDm3Z/B2WVG4smwBkRVm"
            crossorigin="anonymous"></script>
</head>
<body>

    <header>
        @htmxPartial('site-navigation')
    </header>

    <hr>

    <main>
        @htmxPartial('content')
    </main>


</body>
</html>
```

As Elide is configured to look for `resources/views/app.blade.php` by default, we don't need to set anything else up here.

## 2. Create some `Component`s for page content

Let's create 3 pages:

1. Home
2. About
3. The Team

### Create the components

We can do this with `artisan` - it will create the `Component` classes and the templates for us.

```shell
php artisan make:component Content\\Home
php artisan make:component Content\\About
php artisan make:component Content\\TheTeam
```

Update the component templates to include some relevant content. Here's what we've used.

**`resources/views/components/content/home.blade.php`**

```html
<div>
    <h1>Home</h1>
    <p><strong>Welcome to the site!</strong></p>

    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec consequat neque, eleifend fringilla lacus. Nam dapibus tincidunt nunc a sollicitudin. Vestibulum feugiat eu augue nec vulputate. Sed blandit egestas aliquam. Cras luctus lorem et ligula congue, sed imperdiet dui molestie. Sed non sodales metus. Nulla ut pellentesque erat. Integer a egestas diam.</p>

    <p>Proin maximus eros venenatis massa porta, a commodo dolor sollicitudin. Mauris lobortis erat aliquam velit egestas, ac ultricies risus vestibulum. Nam vel imperdiet magna. Phasellus iaculis quam nec lorem dignissim congue. Donec suscipit, neque quis ullamcorper mattis, magna magna imperdiet risus, eget cursus eros ligula vel velit. Mauris eget dictum lacus. Nam malesuada, nunc in ullamcorper sollicitudin, sem tortor sollicitudin velit, et luctus purus ex at nulla.</p>
</div>
```

**`resources/views/components/content/about.blade.php`**

```html
<div>
    <h1>About us</h1>
    <p><strong>Learn more about us!</strong></p>

    <p>Aenean rutrum urna vitae sollicitudin pretium. Nullam feugiat massa rhoncus ligula viverra imperdiet. Maecenas consequat, magna condimentum vulputate gravida, massa turpis gravida ex, et molestie ligula ipsum quis risus. Nulla vel porttitor tortor. Vestibulum quis enim diam. Suspendisse placerat mi ipsum, sed tempor nibh congue sed. Sed iaculis ultrices urna vel faucibus. Suspendisse neque quam, vehicula quis justo id, consequat faucibus odio. Nulla urna tellus, egestas vitae volutpat dapibus, molestie id tellus.</p>

    <p>Vivamus quis ultrices risus, eu pretium lorem. Donec sed facilisis sem. Pellentesque mattis, ligula at hendrerit iaculis, massa nisl facilisis mi, ultricies viverra magna mauris sed velit. Nam ante lectus, suscipit sit amet sem nec, tincidunt vestibulum risus. Etiam egestas sapien ut felis ullamcorper hendrerit. Praesent cursus finibus ultricies. Etiam sed enim hendrerit, hendrerit arcu eget, rhoncus ante. Donec iaculis accumsan velit vitae tristique. Phasellus ut malesuada nisl. Mauris sollicitudin libero ante, ac posuere quam tincidunt ut. Curabitur vel tellus blandit, volutpat dui a, pulvinar massa. Nunc fringilla sem quis sagittis venenatis.</p>

</div>
```

**`resources/views/components/content/the-team.blade.php`**

```html
<div>
    <h1>The team</h1>
    <p><strong>Here are some people who work here.</strong></p>

    <ul>
        <li>Person One</li>
        <li>Person Two</li>
        <li>Person Three</li>
        <li>Person Four</li>
    </ul>

</div>
```

## 3. Create routes for our pages

People cannot access these pages unless we create some routes for them. Let's do that.

Open `routes/web.php` and update it to the following:

```php
<?php

use Elide\Htmx;
use App\View\Components\Content\About;
use App\View\Components\Content\Home;
use App\View\Components\Content\TheTeam;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => Htmx::render(Home::class))->name('home');
Route::get('about', fn() => Htmx::render(About::class))->name('about');
Route::get('the-team', fn() => Htmx::render(TheTeam::class))->name('the-team');
```

At this point if you access `/` or `/about` or `/the-team` in your browser, you will notice that the full root template is returned and the corresponding page content is included where the root template has `@htmxPartial('content')`

We need some navigation though...

## 4. Adding site navigation

This site is pretty useless without navigation. Let's do that now.
### Creating the `Component`

Let's create the `SiteNavigation` `Component`:

```shell
php artisan make:component Ui\\SiteNavigation
```

**`resources/views/components/ui/site-navigation.blade.php`**

```html
<nav>
    <ol>
        <li>
            @if(Route::is('home'))
                <strong>Home</strong>
            @else
                <a href="{{route('home')}}">Home</a>
            @endif
        </li>
        <li>
            @if(Route::is('about'))
                <strong>About</strong>
            @else
                <a href="{{route('about')}}">About</a>
            @endif
        </li>
        <li>
            @if(Route::is('the-team'))
                <strong>The team</strong>
            @else
                <a href="{{route('the-team')}}">The team</a>
            @endif
        </li>
    </ol>
</nav>
```

### Including the site navigation Partial

Now we need to register this component with Elide so that it can be injected into our page where we have `@htmxPartial('site-navigation')`.

We can do this easily by updating our `AppServiceProvider`.

Update `App\Providers\AppServiceProvider.php` to look like the following:

```php
namespace App\Providers;

use App\View\Components\Ui\SiteNavigation;
use Elide\Htmx;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Htmx::usingPartials(fn() => [
            SiteNavigation::class,
        ]);
    }
}
```

We can give callbacks to `Htmx::usingPartials()` which return arrays of `View`s or `Component`s. Elide will automatically turn those into `Partial`s which get included in the response to the browser.

> [!NOTE]
> It is important to be mindful about which partials are registered from your app's service provider. Generally you will only want to register partials which should be included in pretty much _every_ response. If you need additional partials (such as a toast notification), you can add those in your routes where/when required.

If you view the site now, we have a (very basic) layout with content and navigation which we can click around. 🎉

**But hang on!...** These are all full page responses, there's nothing dynamic about this... If we look at the network responses the full `<html> ... </html>` is always included.

Let's fix that with HTMX.

## 5. Making site navigation dynamic

When someone uses the site navigation we want to avoid a full page refresh and only want to update the relevant parts of our page: content, and site navigation.

Elide + HTMX makes this trivial.

Jump back into the `site-navigation.blade.php` file and add the `hx-boost` attribute to the `<nav>` element.

`resources/views/components/ui/site-navigation.blade.php`

```html
<nav hx-boost="true">
    <ol>
        <li>
            @if(Route::is('home'))
                <strong>Home</strong>
            @else
                <a href="{{route('home')}}">Home</a>
            @endif
        </li>
        <li>
            @if(Route::is('about'))
                <strong>About</strong>
            @else
                <a href="{{route('about')}}">About</a>
            @endif
        </li>
        <li>
            @if(Route::is('the-team'))
                <strong>The team</strong>
            @else
                <a href="{{route('the-team')}}">The team</a>
            @endif
        </li>
    </ol>
</nav>
```

🎉  Now when you navigate around the site we have something more dynamic!

If you have a look at the network requests you will now spot that the response only includes HTML for the site navigation and the page's content. 

HTMX has replaced those instances in the page with the updated versions. There are no refreshes, no refetches of JS or CSS assets, and the URL and browser history has been updated.

But there's one last thing we need to deal with - none of our pages have a `<title>` set

## Setting page titles

Elide handles updating page titles for full page responses and HTMX AJAX responses by using a `$title` property in the root application view.

E.g., this part of `app.blade.php`:

```html
<title>{{$title ?? 'no title set'}}</title>
```

We can set a title really easily from our routes by using `Htmx::render()->title(...)`.

Let's update `routes/web.php`:

```php
Route::get(
    "/",
    fn() => Htmx::render(Home::class)->title("Home")
)->name("home");

Route::get(
    "about",
    fn() => Htmx::render(About::class)->title("About")
)->name("about");

Route::get(
    "the-team",
    fn() => Htmx::render(TheTeam::class)->title("The team")
)->name("the-team");

```

