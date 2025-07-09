<?php

declare(strict_types=1);

namespace Workbench\App\View\Components;

use Elide\Contracts\ComponentSpecifiesSwapStrategy;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TestSpecifiedSwapTargetComponent extends Component implements ComponentSpecifiesSwapStrategy
{
    public function render(): View
    {
        return view('test::test-component');
    }

    public function swapStrategy(): string
    {
        return 'afterEnd swap:2s';
    }
}
