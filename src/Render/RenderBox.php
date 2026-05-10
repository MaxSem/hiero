<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

use DOMElement;
use MaxSem\Hiero\ViewBox;

final readonly class RenderBox
{
    public function __construct(
        public DOMElement $output,
        public ViewBox $viewBox,
    ) {
    }
}
