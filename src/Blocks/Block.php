<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

abstract readonly class Block
{
    abstract public function markup(): string;
}
