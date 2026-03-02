<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

abstract readonly class Operator extends Block
{
    public function __construct(
        public Block $left,
        public Block $right,
    ) {
    }
}
