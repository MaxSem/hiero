<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

final readonly class Size
{
    public function __construct(
        public float $width,
        public float $height,
    ) {
    }
}
