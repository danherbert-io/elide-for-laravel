<?php

declare(strict_types=1);

namespace Workbench\App\View\Components;

use Elide\Contracts\ProvidesPartialName;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TestWithProvidedNameComponent extends Component implements ProvidesPartialName
{
    public function render(): View
    {
        return view('test::test-component');
    }

    public function partialName(): string
    {
        return 'custom-test-partial-name';
    }
}
