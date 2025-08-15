
# Changelog

## [v1.0.9](https://github.com/danherbert-io/elide-for-laravel/compare/v1.0.8...v1.0.9) - 2025-08-15

Add a `filteringPartials()` method to the `HtmxResponse` object, so that partials can be filtered for HTMX responses.

## [v1.0.8](https://github.com/danherbert-io/elide-for-laravel/compare/v1.0.7...v1.0.8) - 2025-08-01

Update partials to define a `hx-headers="{'X-Elide-Partial-Id':...'}""` attribute, and provide methods to `HtmxResponse`, so that responses can be easily scoped to specific partials.

See [Scoping the response to specific partials](https://github.com/danherbert-io/elide-for-laravel/blob/1.x/docs/digging-deeper/htmx-response.md#scoping-the-response-to-specific-partials).

## [v1.0.7](https://github.com/danherbert-io/elide-for-laravel/compare/v1.0.6...v1.0.7) - 2025-07-30

Update `HtmxResponse::toResponse()` to progressively merge rendered partials through `View::share()` - enables support for nested partials.

## [v1.0.6](https://github.com/danherbert-io/elide-for-laravel/compare/v1.0.0...v1.0.6) - 2025-07-25

Additional documentation, improved resolution of `HtmxRequest`, improved sharing of partials for nested components, improved handling of HTMX OOB attribute for non-AJAX/AJAX requests (solves "vanishing islands" when using browser back navigation).


## v1.0.0 - 2025-07-15

Initial release