<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

final class Position
{
    public function __construct(
        public readonly int $x,
        public readonly int $y,
    ) {
    }
}
