<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

use MaxSem\Hiero\Font;

readonly class Options
{
    public function __construct(
        public Font $font,
        public RenderMode $mode = RenderMode::SVG,
    ) {
    }
}
