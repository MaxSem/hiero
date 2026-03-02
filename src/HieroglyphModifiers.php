<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

final readonly class HieroglyphModifiers
{
    public function __construct(
        public string $markup,
        public int $rotation,
        public bool $mirror,
    ) {
    }
}
