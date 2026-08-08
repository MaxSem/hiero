<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

use MaxSem\Hiero\Error;
use MaxSem\Hiero\ViewBox;

final readonly class RenderOutput
{
    public function __construct(
        public string $svg,
        public ViewBox $viewBox,
        /**
         * @var array<Error>
         */
        public array $errors,
        /**
         * @var int Height of a single character in SVG units, useful for scaling the SVG on the page
         */
        public int $charHeight,
    ) {
    }
}
