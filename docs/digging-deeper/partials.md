# Partials

A Partial is essentially a self contained HTML fragment. Most often it will closely align with a component of some sort.

Some examples of what a Partial might contain:

- Site navigation
- Shopping cart widget
- Product details panel
- Table of users
- Search form
- Company edit form
- Billing details
- etc

Partials can be included as part of a full page render, or returned via AJAX where they will be automatically injected by HTMX into the DOM in the appropriate place.

The primary goal of Elide's Partials is to take care of HTMX's out of band swapping requirements, allowing you to focus primarily on your `View`s/`Component`s and not worry about how they'll dynamically wire into the frontend. 

## Placing Partials in your templates

Elide's `@htmxPartial()` Blade directive makes it simple to declare where Partials should be rendered in your HTML.

For example:

```html
<body>
	<header>
		<x-application-logo />
		@htmxPartial('primary-navigation')
	</header>

	<div id="main">
		@htmxPartial('content')
	</div>
</body>
```

> [!TIP] `@htmxPartial('content')` is the "primary" Partial used with Elide's application view. Whatever component is provided to `Htmx::render(...)` will be used for the `'content'` Partial

## The `Partial` class

The `\Elide\View\Partial` class is a thin wrapper around Laravel `View`s and `Component`s, and is the main "glue" which helps HTMX update the correct parts of the HTML frontend when required.

> [!IMPORTANT] It is important to understand how HTMX's [`hx-swap`](https://htmx.org/attributes/hx-swap/) and [`hx-swap-oob`](https://htmx.org/attributes/hx-swap-oob/) attributes work. They are a fundamental part of how Elide's `Partial`s work.
### Manually creating a `Partial`

The `HTMX::render(...)` method will take care of instantiating `Partial`s for you in many cases, though there may be times where you may need to do it yourself.

You can instantiate a `Partial` in a number of ways:

```php
use Elide\Htmx;
use App\View\Components\Ui\User\ProfileForm;

// Create a Partial from a view name
$partial = Htmx::partial('ui.user.profile-form');

// Create a Partial from a view
$partial = Htmx::partial(view('ui.user.profile-form'));

// Create a Partial from a Component class name
$partial = Htmx::partial(ProfileForm::class)

// Create a Partial from an instantiated Component
$partial = Htmx::partial(new ProfileForm)
```

`Partial`s must have a name with which they can be referred. The `Htmx::partial(...)` method will automatically determine a name for you, though you can also specify one yourself:

```php
use Elide\View\Partial;
use App\View\Components\Ui\User\ProfileForm;

// -- Automatic naming --

// Create a Partial from a view name
$partial = Htmx::partial('ui.user.profile-form');
// name = 'ui-user-profile-form'

// Create a Partial from a view
$partial = Htmx::partial(view('ui.user.profile-form'));
// name = 'ui-user-profile-form'

// Create a Partial from a Component class name
$partial = Htmx::partial(ProfileForm::class)
// name = 'profile-form'

// Create a Partial from an instantiated Component
$partial = Htmx::partial(new ProfileForm)
// name = 'profile-form'

// -- Custom naming --
$partial = Htmx::partial(ProfileForm::class, name: 'subscriber-profile-form')
```

If you use `new Partial(ProfileForm::class, name: 'profile-form')`, the name parameter is required.

You can also provide properties to be provided when a `Partial`'s `View`/`Component` is being instantiated and/or rendered:

```php
$partial = Htmx::partial(ProfileForm::class, props: [
	'user' => $user
])
```

> [!TIP] When a `Component` class name is provided to `Htmx::partial()`, Elide will use Laravel's app container to resolve the provided `Component` class. If the `Component` uses dependency injection to access services, request objects, etc, those will be automagically injected - you don't need to pass them into `Htmx::partial(..., props: [...])`
### The `Partial`'s "glue"

When a `Partial` is rendered, it renders its `View`/`Component` and wraps that HTML in a special container element. That container element has attributes defined to help HTMX update the frontend while also having minimal impact on the HTML's flow and layout.

The rendered output of a `Partial` will vary, though will usually look something like this:

```html
<div id="partial:subscriber-profile-form" 
     style="display: contents;" 
     hx-swap-oob="true">
	... the rendered view/component ...
</div>
```

- The `"subscriber-profile-form"` part of the `id` attribute is the `Partial`'s name.
- `style="display: contents;"` removes the wrapping `<div>` from the layout, making its children behave as if they were direct children of the parent. As such the `<div>` itself is not rendered, and the children remain in the flow of the parent and inherit styling normally.
- `hx-swap-oob="true"` instructs HTMX to find an element in the page with the same ID and replace that with the incoming `Partial`

### Customising the HTMX OOB swap strategy

By default a `Partial`'s swap strategy matches HTMX's default: `hx-swap-oob="true"`.

You may specify an alternate strategy by updating your `Component` to implement the `Elide\Contracts\ComponentSpecifiesSwapStrategy` contract.

For example:

```php
use Elide\Contracts\ComponentSpecifiesSwapStrategy;  
use Illuminate\View\Component;  
  
class SampleComponent extends Component implements ComponentSpecifiesSwapStrategy  
{

	public function swapStrategy(): string
	{
		return 'beforeend:body';
	}

}
```

The resultant partial HTML fragment might then look like this:
```html
<div id="partial:sample-component" 
     style="display: contents;" 
     hx-swap-oob="beforeend:body">
	... the rendered view/component ...
</div>
```

HTMX would then take the rendered view/component HTML and append that to the end of the `<body>` element.