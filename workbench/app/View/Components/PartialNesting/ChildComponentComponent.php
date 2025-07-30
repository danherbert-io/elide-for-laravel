<?php

declare(strict_types=1);

namespace Workbench\App\View\Components\PartialNesting;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ChildComponentComponent extends Component
{
    public function render(): View
    {
        return view('test::partial-nesting.child-component');
    }
}
