<?php

declare(strict_types=1);

namespace Workbench\App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class TestComponent extends Component
{
    public function __construct(
        public readonly mixed $prop = null,
    ) {}

    public function render(): View
    {
        return view('test::test-component');
    }
}
