<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

final class Size
{
    public function __construct(
        public readonly float $width,
        public readonly float $height,
    ) {
    }
}
