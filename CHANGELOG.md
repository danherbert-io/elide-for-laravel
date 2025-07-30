
# Changelog

## [v1.0.7](https://github.com/danherbert-io/elide-for-laravel/compare/v1.0.6...v1.0.7) - 2025-07-30

Update `HtmxResponse::toResponse()` to progressively merge rendered partials through `View::share()` - enables support for nested partials.

## [v1.0.6](https://github.com/danherbert-io/elide-for-laravel/compare/v1.0.0...v1.0.6) - 2025-07-25

Additional documentation, improved resolution of `HtmxRequest`, improved sharing of partials for nested components, improved handling of HTMX OOB attribute for non-AJAX/AJAX requests (solves "vanishing islands" when using browser back navigation).


## v1.0.0 - 2025-07-15

Initial release