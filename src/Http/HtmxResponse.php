<?php

declare(strict_types=1);

namespace Elide\Http;

use Elide\Enums\HtmxTriggerTiming;
use Elide\View\Partial;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\ResponseTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View as IlluminateView;
use Illuminate\View\Component;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class HtmxResponse implements Responsable
{
    use ResponseTrait;

    /**
     * An array of callables which will return an array of Partials.
     *
     * @var array<callable():(array<int, Partial|View|Component|string>)>
     */
    protected array $usingPartials = [];

    /**
     * The main partial to be rendered with this response.
     */
    protected ?Partial $partial = null;

    /**
     * The HtmxRequest which the response will use to determine its responding content.
     *
     * @var HtmxRequest|\Illuminate\Http\Request
     */
    public readonly HtmxRequest $request;

    /**
     * Headers to be returned.
     */
    protected ResponseHeaderBag $headers;

    /**
     * Status code of the response.
     */
    protected int $status = SymfonyResponse::HTTP_OK;

    /**
     * Optional title to return to the HTMX frontend.
     */
    protected ?string $title = null;

    /**
     * Whether the response should only return the origin Partial if it was provided.
     */
    protected bool $scopeToRequestingPartial = false;

    /**
     * Instantiate a new HTMX Response.
     */
    public function __construct(
        public readonly null|Partial|View|Component|string $component = null,
        public readonly array $props = [],
        public readonly string $rootView = 'app',
        public readonly ?string $partialName = null,
        public readonly bool $sourceElementSwap = false,
        int $status = SymfonyResponse::HTTP_OK,
        array $headers = [],
    ) {
        $this->headers = new ResponseHeaderBag($headers);
        if ($this->component) {
            $this->partial = Partial::resolveFrom($component, $props, $this->partialName);
        }
        $this->request = app(HtmxRequest::class);
        $this->status($status);
    }

    /**
     * Set the status code.
     *
     * @return $this
     */
    public function status(int $code): static
    {
        if (! array_key_exists($code, SymfonyResponse::$statusTexts)) {
            throw new \InvalidArgumentException(sprintf(
                'Provided code "%s" is not a valid HTTP status code.',
                $code,
            ));
        }

        $this->status = $code;

        return $this;
    }

    /**
     * Create an HTTP response for HTMX. If the request was an HTMX AJAX request, only partials will be returned. If it
     * was not an HTMX AJAX request, a full render of the main partial will be returned. If a title has been specified
     * that will be injected into the response content.
     */
    public function toResponse($request)
    {
        $sharedProps = ['partials' => []];

        /** @var Collection $partials */
        $partials =
            collect($this->usingPartials)
                ->map(fn (callable $partial) => $partial())
                ->flatten(1)
                ->map(fn (Partial|View|Component|string $partial) => Partial::resolveFrom($partial))
                ->when($this->component, function (Collection $collection) {
                    $collection->push($this->partial);
                })
                ->groupBy(fn (Partial $partial) => $partial->name)
                ->map(function (Collection $group, string $key) use (&$sharedProps) {
                    $renderedGroup = $group->map->render()->join("\n");
                    $sharedProps['partials'][$key] = $renderedGroup;

                    // Progressively share rendered partials for upcoming components/partials to be rendered.
                    IlluminateView::share($sharedProps);

                    return $renderedGroup;
                });

        if ($this->request->isHtmxRequest()) {
            if ($this->scopeToRequestingPartial && $partials->has($partialId = $this->request->partialId())) {
                // @TODO Consider if we can optimise how we isolate these islands. Nested partials necessitate that we
                //       need to still render all partials, even though we're scoping down to a single one for the
                //       response.
                $partials = $partials->only($partialId);
            }

            return response(
                content: $partials
                    ->when($this->title, function (Collection $collection) {
                        return $collection->prepend(sprintf(
                            '<title>%s</title>',
                            e($this->title),
                        ));
                    })
                    ->flatten()
                    ->join("\n"),
                status: $this->status,
                headers: $this->headers->all(),
            );
        }

        if (! $this->component) {
            return response(
                content: null,
                status: $this->status,
                headers: $this->headers->all(),
            );
        }

        if ($this->title) {
            $sharedProps['title'] = $this->title;
        }

        IlluminateView::share($sharedProps);

        return Response::view(
            $this->rootView,
            $sharedProps,
            status: $this->status,
            headers: $this->headers->all(),
        );
    }

    /**
     * Set the title to be sent with the response.
     *
     * @return $this
     */
    public function title(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Specify a callable which will be used to provide partials to the response when it is rendered.
     *
     * @param  callable():(array<int, Partial|View|Component|string>)  $callable
     * @return $this
     */
    public function usingPartials(callable $callable): static
    {
        $this->usingPartials[] = $callable;

        return $this;
    }

    /**
     * Specify a location for HTMX to navigate to.
     *
     * @see https://htmx.org/headers/hx-location/
     */
    public function location(string $path, ?string $target = null): static
    {
        if (is_null($target)) {
            $this->headers->set('HX-Location', $path);
        } else {
            $this->headers->set('HX-Location', json_encode(compact('path', 'target')));
        }

        return $this;
    }

    /**
     * Specify a URL for HTMX to push.
     *
     * @see https://htmx.org/headers/hx-push-url/
     */
    public function pushUrl(false|string $url): static
    {
        $this->headers->set('HX-Push-Url', $url === false ? 'false' : $url);

        return $this;
    }

    /**
     * Specify a URL for HTMX to perform a full redirect to.
     *
     * @see https://htmx.org/headers/hx-redirect/
     */
    public function redirect(string $url): static
    {
        $this->headers->set('HX-Redirect', $url);

        return $this;
    }

    /**
     * Specify to HTMX that the page should be refreshed.
     *
     * @see https://htmx.org/headers/hx-refresh/
     */
    public function refresh(): static
    {
        $this->headers->set('HX-Refresh', 'true');

        return $this;
    }

    /**
     * Instruct HTMX to replace the URL with the provided URL.
     *
     * @see https://htmx.org/headers/hx-replace-url/
     */
    public function replaceUrl(false|string $url): static
    {
        $this->headers->set('HX-Replace-Url', $url === false ? 'false' : $url);

        return $this;
    }

    /**
     * Instruct HTMX with a new swap target.
     *
     * @see https://htmx.org/docs/#response-headers
     */
    public function reswap(string $swap): static
    {
        $this->headers->set('HX-Reswap', $swap);

        return $this;
    }

    /**
     * Retarget HTMX.
     *
     * @see https://htmx.org/docs/#response-headers
     */
    public function retarget(string $cssSelector): static
    {
        $this->headers->set('HX-Retarget', $cssSelector);

        return $this;
    }

    /**
     * Instruct HTMX to reselect.
     *
     * @see https://htmx.org/docs/#response-headers
     */
    public function reselect(string $cssSelector): static
    {
        $this->headers->set('HX-Reselect', $cssSelector);

        return $this;
    }

    /**
     * Send a trigger to HTMX.
     *
     * @see https://htmx.org/headers/hx-trigger/
     */
    public function trigger(string|array $event, HtmxTriggerTiming $when = HtmxTriggerTiming::IMMEDIATELY): static
    {
        $this->headers->set(
            $when->header(),
            is_string($event) ? $event : json_encode($event),
        );

        return $this;
    }

    /**
     * Specify if the response should be scoped to the requesting partial, if the request is a HTMX request and an
     * originating Partial was specified. If no matching partial is provided, the full set will be returned.
     */
    public function scopeToRequestingPartial(bool $shouldScope = true): static
    {
        $this->scopeToRequestingPartial = $shouldScope;

        return $this;
    }
}
