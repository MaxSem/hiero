<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

final class Position
{
    public function __construct(
        public readonly float $x,
        public readonly float $y,
    ) {
    }
}
