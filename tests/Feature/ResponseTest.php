<?php

declare(strict_types=1);

namespace Feature;

use Elide\Enums\HtmxTriggerTiming;
use Elide\Enums\RequestKind;
use Elide\Htmx;
use Elide\Http\HtmxResponse;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;
use Tests\TestCase;
use Workbench\App\View\Components\AlternateTestComponent;
use Workbench\App\View\Components\TestComponent;

class ResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Htmx::rootView('test::app');

        Route::get('test', function () {
            $propValue = request()->get('prop');

            return Htmx::render(TestComponent::class, props: [
                'prop' => $propValue,
            ]);
        });
    }

    public function test_it_returns_full_response(): void
    {
        $response = $this->get('test');
        $response->assertStatus(200);

        $content = trim($response->getContent());

        $partial = Htmx::partial(TestComponent::class, name: 'content')->render();

        $this->assertStringStartsWith('<html>', $content);
        $this->assertStringEndsWith('</html>', $content);

        $this->assertStringContainsString($partial, $content);
    }

    public function test_it_returns_partial_only_response(): void
    {
        $response = $this
            ->withHeaders([
                'HX-Request' => 'true',
            ])
            ->get('test');

        $response->assertStatus(200);

        $partial = Htmx::partial(TestComponent::class, name: 'content')->render();
        $content = trim($response->getContent());

        $this->assertSame($content, $partial);
    }

    public function test_it_returns_full_response_with_prop_value(): void
    {
        $response = $this->get('test?prop=123');
        $response->assertStatus(200);

        $content = trim($response->getContent());

        $partial = Htmx::partial(
            TestComponent::class,
            props: ['prop' => '123'],
            name: 'content',
        )->render();

        $this->assertStringStartsWith('<html>', $content);
        $this->assertStringEndsWith('</html>', $content);

        $this->assertStringContainsString($partial, $content);
    }

    public function test_it_returns_partial_only_response_with_prop_value(): void
    {
        $response = $this
            ->withHeaders([
                'HX-Request' => 'true',
            ])
            ->get('test?prop=123');

        $response->assertStatus(200);

        $partial = Htmx::partial(
            TestComponent::class,
            props: ['prop' => '123'],
            name: 'content',
        )->render();
        $content = trim($response->getContent());

        $this->assertSame($content, $partial);
    }

    public function test_it_includes_service_level_partials_for_ajax_responses(): void
    {
        Htmx::usingPartials(fn () => [
            'alternate-test-component' => Htmx::partial(AlternateTestComponent::class),
        ]);

        $response = $this->withHeaders(['HX-Request' => 'true'])->get('test');

        $response->assertStatus(200);

        $contentPartial = Htmx::partial(TestComponent::class, name: 'content')->render();
        $extraPartial = Htmx::partial(AlternateTestComponent::class)->render();

        $content = trim($response->getContent());

        $this->assertStringContainsString($contentPartial, $content);
        $this->assertStringContainsString($extraPartial, $content);

        $this->assertStringNotContainsString('<html>', $content);
    }

    public function test_it_returns_ajax_only_service_level_partials_appropriately(): void
    {
        Htmx::usingPartials(fn () => [
            'alternate-test-component' => Htmx::partial(AlternateTestComponent::class),
        ], for: RequestKind::AJAX);

        $ajaxPartial = Htmx::partial(AlternateTestComponent::class)->render();

        $response = $this->get('test');
        $response->assertStatus(200);

        $content = trim($response->getContent());

        $this->assertStringNotContainsString($ajaxPartial, $content);

        $response = $this->withHeaders(['HX-Request' => 'true'])->get('test');
        $response->assertStatus(200);

        $content = trim($response->getContent());

        $this->assertStringContainsString($ajaxPartial, $content);
    }

    public function test_it_returns_non_ajax_only_service_level_partials_appropriately(): void
    {
        Htmx::usingPartials(fn () => [
            'alternate-test-component' => Htmx::partial(AlternateTestComponent::class),
        ], for: RequestKind::NON_AJAX);

        $ajaxPartial = Htmx::partial(AlternateTestComponent::class)->render();

        $response = $this->get('test');
        $response->assertStatus(200);

        $content = trim($response->getContent());

        $this->assertStringContainsString($ajaxPartial, $content);

        $response = $this->withHeaders(['HX-Request' => 'true'])->get('test');
        $response->assertStatus(200);

        $content = trim($response->getContent());

        $this->assertStringNotContainsString($ajaxPartial, $content);
    }

    public function test_it_returns_ajax_and_non_ajax_only_service_level_partials_appropriately(): void
    {
        Htmx::usingPartials(fn () => [
            'alternate-test-component' => Htmx::partial(AlternateTestComponent::class),
        ]);

        $ajaxPartial = Htmx::partial(AlternateTestComponent::class)->render();

        $response = $this->get('test');
        $response->assertStatus(200);

        $content = trim($response->getContent());

        $this->assertStringContainsString($ajaxPartial, $content);

        $response = $this->withHeaders(['HX-Request' => 'true'])->get('test');
        $response->assertStatus(200);

        $content = trim($response->getContent());

        $this->assertStringContainsString($ajaxPartial, $content);
    }

    public function test_it_sends_location(): void
    {
        $response = (new HtmxResponse)
            ->location('the-location')
            ->toResponse(request());
        $this->assertSame('the-location', $response->headers->get('HX-Location'));
    }

    public function test_it_sends_location_with_target(): void
    {
        $response = (new HtmxResponse)
            ->location('the-location', '#the-target')
            ->toResponse(request());
        $this->assertSame('{"path":"the-location","target":"#the-target"}', $response->headers->get('HX-Location'));
    }

    public function test_it_sends_push_url(): void
    {
        $response = (new HtmxResponse)
            ->pushUrl('the-new-url')
            ->toResponse(request());
        $this->assertSame('the-new-url', $response->headers->get('HX-Push-Url'));
    }

    public function test_it_sends_push_url_false(): void
    {
        $response = (new HtmxResponse)
            ->pushUrl(false)
            ->toResponse(request());
        $this->assertSame('false', $response->headers->get('HX-Push-Url'));
    }

    public function test_it_sends_redirect(): void
    {
        $response = (new HtmxResponse)
            ->redirect('the-new-url')
            ->toResponse(request());
        $this->assertSame('the-new-url', $response->headers->get('HX-Redirect'));
    }

    public function test_it_sends_refresh(): void
    {
        $response = (new HtmxResponse)
            ->refresh()
            ->toResponse(request());
        $this->assertSame('true', $response->headers->get('HX-Refresh'));
    }

    public function test_it_replaces_url(): void
    {
        $response = (new HtmxResponse)
            ->replaceUrl('the-new-url')
            ->toResponse(request());
        $this->assertSame('the-new-url', $response->headers->get('HX-Replace-Url'));
    }

    public function test_it_replaces_url_false(): void
    {
        $response = (new HtmxResponse)
            ->replaceUrl(false)
            ->toResponse(request());
        $this->assertSame('false', $response->headers->get('HX-Replace-Url'));
    }

    public function test_it_reswaps(): void
    {
        $response = (new HtmxResponse)
            ->reswap('swap-strategy')
            ->toResponse(request());
        $this->assertSame('swap-strategy', $response->headers->get('HX-Reswap'));
    }

    public function test_it_retargets(): void
    {
        $response = (new HtmxResponse)
            ->retarget('#new-target')
            ->toResponse(request());
        $this->assertSame('#new-target', $response->headers->get('HX-Retarget'));
    }

    public function test_it_reselects(): void
    {
        $response = (new HtmxResponse)
            ->reselect('#new-target')
            ->toResponse(request());
        $this->assertSame('#new-target', $response->headers->get('HX-Reselect'));
    }

    public function test_it_triggers_simple_events(): void
    {
        foreach (HtmxTriggerTiming::cases() as $when) {
            $response = (new HtmxResponse)
                ->trigger('the-event from:body', $when)
                ->toResponse(request());
            $this->assertSame('the-event from:body', $response->headers->get($when->header()));
        }
    }

    public function test_it_triggers_detailed_events(): void
    {
        $event = [
            'show-message' => 'The message',
        ];
        foreach (HtmxTriggerTiming::cases() as $when) {
            $response = (new HtmxResponse)
                ->trigger($event, $when)
                ->toResponse(request());

            $this->assertSame(json_encode($event), $response->headers->get($when->header()));
        }
    }

    public function test_it_triggers_multiple_detailed_events(): void
    {
        $event = [
            'show-message' => 'The message',
            'highlight-element' => [
                'target' => '#the-element',
            ],
        ];
        foreach (HtmxTriggerTiming::cases() as $when) {
            $response = (new HtmxResponse)
                ->trigger($event, $when)
                ->toResponse(request());

            $this->assertSame(json_encode($event), $response->headers->get($when->header()));
        }
    }

    public function test_it_sends_partials_with_response(): void
    {
        $partial = Htmx::partial(AlternateTestComponent::class)->render();

        Htmx::sendWithResponse(AlternateTestComponent::class);

        $response = $this
            ->withHeaders([
                'HX-Request' => 'true',
            ])
            ->get('test');

        $response->assertStatus(200);

        $content = trim($response->getContent());

        $this->assertStringContainsString($partial, $content);

        // As the partial was sent with the previous response, it should not be sent with the following one.
        $response = $this
            ->withHeaders([
                'HX-Request' => 'true',
            ])
            ->get('test');

        $response->assertStatus(200);

        $content = trim($response->getContent());

        $this->assertStringNotContainsString($partial, $content);
    }

    public function test_it_sends_single_partials(): void
    {
        $view = view('test::alternate-test-component');

        Htmx::sendWithResponse($view);

        $pendingPartials = Htmx::flushPendingPartials();

        $this->assertCount(1, $pendingPartials);
        $this->assertSame($view, $pendingPartials[0]);
    }

    public function test_it_sends_multiple_partials(): void
    {
        $viewA = view('test::alternate-test-component');
        $viewB = view('test::test-component');

        Htmx::sendWithResponse([$viewA, $viewB]);

        $pendingPartials = Htmx::flushPendingPartials();

        $this->assertCount(2, $pendingPartials);
        $this->assertSame($viewA, $pendingPartials[0]);
        $this->assertSame($viewB, $pendingPartials[1]);
    }

    public function test_it_throws_when_sending_invalid_partials_via_array(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only Partials, Views, or Components can be returned with HTMX responses');

        Htmx::sendWithResponse([app()]);
    }

    public function test_it_renders_string_view_with_provided_props(): void
    {
        $result = Htmx::partial(
            'test::test-component',
            props: [
                'prop' => '123',
            ]
        )->render();

        $this->assertStringContainsString('Prop value: 123', $result);
    }

    public function test_it_renders_with_traditional_view_props(): void
    {
        $result = Htmx::partial(
            view('test::test-component', [
                'prop' => '123',
            ])
        )->render();

        $this->assertStringContainsString('Prop value: 123', $result);
    }

    public function test_it_renders_with_traditional_view_with_props(): void
    {
        $result = Htmx::partial(
            view('test::test-component')->with([
                'prop' => '123',
            ])
        )->render();

        $this->assertStringContainsString('Prop value: 123', $result);
    }

    public function test_it_renders_view_with_provided_props(): void
    {
        $result = Htmx::partial(
            view('test::test-component'),
            props: [
                'prop' => '123',
            ]
        )->render();

        $this->assertStringContainsString('Prop value: 123', $result);
    }

    public function test_it_renders_component_with_traditional_props(): void
    {
        $result = Htmx::partial(new TestComponent(prop: '123'))->render();

        $this->assertStringContainsString('Prop value: 123', $result);
    }

    public function test_it_renders_component_with_provided_props(): void
    {
        $result = Htmx::partial(
            new TestComponent,
            props: [
                'prop' => '123',
            ]
        )->render();

        $this->assertStringContainsString('Prop value: 123', $result);
    }

    public function test_it_renders_component_class_with_provided_props(): void
    {
        $result = Htmx::partial(
            TestComponent::class,
            props: [
                'prop' => '123',
            ]
        )->render();

        $this->assertStringContainsString('Prop value: 123', $result);
    }
}
