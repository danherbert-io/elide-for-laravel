<?php

declare(strict_types=1);

namespace Feature;

use Elide\Htmx;
use Elide\View\Partial;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\ViewException;
use Tests\TestCase;
use Workbench\App\View\Components\TestComponent;

class BladeDirectiveTest extends TestCase
{
    public function test_it_throws_for_non_string_keys(): void
    {
        $template = '@htmxPartial(123)';

        $this->expectException(ViewException::class);
        $this->expectExceptionMessage('Key should be a string');

        Blade::render($template);
    }

    public function test_it_renders_undeclared_partials(): void
    {
        $template = "@htmxPartial('the-partial')";

        $result = Blade::render($template);
        $expected = (new Partial('', 'the-partial'))->render();

        $this->assertSame($expected, $result);
    }

    public function test_it_renders_view_partials(): void
    {
        $template = "@htmxPartial('test-component')";

        $view = view('test::test-component');

        $result = Blade::render($template, [
            'partials' => [
                'test-component' => Htmx::partial($view, name: 'test-component')->render(),
            ],
        ]);

        $expected = (Htmx::partial($view, name: 'test-component'))->render();

        $this->assertSame($expected, $result);
    }

    public function test_it_renders_component_partials(): void
    {
        $template = "@htmxPartial('test-component')";

        $result = Blade::render($template, [
            'partials' => [
                'test-component' => Htmx::partial(new TestComponent)->render(),
            ],
        ]);

        $expected = (Htmx::partial(new TestComponent))->render();

        $this->assertSame($expected, $result);
    }
}
