<?php

declare(strict_types=1);

namespace Feature;

use Elide\Enums\Headers;
use Elide\Htmx;
use Tests\TestCase;
use Workbench\App\View\Components\TestComponent;
use Workbench\App\View\Components\TestSpecifiedSwapTargetComponent;
use Workbench\App\View\Components\TestWithProvidedNameComponent;

class PartialTest extends TestCase
{
    public function test_it_resolves_view_name_correctly(): void
    {
        $partial = Htmx::partial(view('test::test-component'));
        $this->assertSame('test-test-component', $partial->name);
    }

    public function test_it_resolves_component_name_correctly(): void
    {
        $partial = Htmx::partial(TestComponent::class);
        $this->assertSame('test-component', $partial->name);
    }

    public function test_it_resolves_component_provided_name_correctly(): void
    {
        $partial = Htmx::partial(new TestWithProvidedNameComponent);
        $this->assertSame($partial->name, (new TestWithProvidedNameComponent)->partialName());
    }

    public function test_it_renders_by_view_name(): void
    {
        $content = Htmx::partial('test::test-component')->render();

        $viewContent = view('test::test-component')->render();

        $this->assertStringContainsString($viewContent, $content);
        $this->assertStringContainsString('id="partial:test-test-component"', $content);
    }

    public function test_it_renders_view(): void
    {
        $content = Htmx::partial(view('test::test-component'))->render();

        $viewContent = view('test::test-component')->render();

        $this->assertStringContainsString($viewContent, $content);
        $this->assertStringContainsString('id="partial:test-test-component"', $content);
    }

    public function test_it_renders_component_class(): void
    {
        $content = Htmx::partial(TestComponent::class)->render();

        $viewContent = view('test::test-component')->render();

        $this->assertStringContainsString($viewContent, $content);
        $this->assertStringContainsString('id="partial:test-component"', $content);
    }

    public function test_it_renders_component_class_instance(): void
    {
        $content = Htmx::partial(new TestComponent)->render();

        $viewContent = view('test::test-component')->render();

        $this->assertStringContainsString($viewContent, $content);
        $this->assertStringContainsString('id="partial:test-component"', $content);
    }

    public function test_it_defines_no_swap_strategy_when_not_htmx_request(): void
    {
        $content = Htmx::partial('test::test-component')->render();

        $this->assertStringNotContainsString('hx-swap-oob', $content);
    }

    public function test_it_uses_default_swap_strategy(): void
    {
        $request = app('request');
        $request->headers->set('HX-Request', 'true');
        app()->instance('request', $request);

        $content = Htmx::partial('test::test-component')->render();

        $this->assertStringContainsString('hx-swap-oob="true"', $content);
    }

    public function test_it_uses_specified_swap_strategy(): void
    {
        $request = app('request');
        $request->headers->set('HX-Request', 'true');
        app()->instance('request', $request);

        $content = Htmx::partial(TestSpecifiedSwapTargetComponent::class)->render();

        $strategy = (new TestSpecifiedSwapTargetComponent)->swapStrategy();
        $expected = sprintf('hx-swap-oob="%s"', $strategy);

        $this->assertStringContainsString($expected, $content);
    }

    public function test_it_includes_partial_id_header(): void
    {
        $request = app('request');
        $request->headers->set('HX-Request', 'true');
        app()->instance('request', $request);

        $partial = Htmx::partial('test::test-component');

        $attributeValue = e(json_encode([Headers::ELIDE_PARTIAL_ID->value => $partial->name]));
        $attribute = sprintf('hx-headers="%s"', $attributeValue);
        $content = $partial->render();

        $this->assertStringContainsString($attribute, $content);
    }

    public function test_it_includes_partial_id_header_when_partial_name_is_custom(): void
    {
        $request = app('request');
        $request->headers->set('HX-Request', 'true');
        app()->instance('request', $request);

        $customName = 'custom-partial-name';
        $partial = Htmx::partial('test::test-component', name: $customName);

        $attributeValue = e(json_encode([Headers::ELIDE_PARTIAL_ID->value => $partial->name]));
        $attribute = sprintf('hx-headers="%s"', $attributeValue);
        $content = $partial->render();

        $this->assertStringContainsString($customName, $attribute);
        $this->assertStringContainsString($attribute, $content);
    }
}
