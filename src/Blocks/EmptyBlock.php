<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

final readonly class EmptyBlock extends Block
{
    public function markup(): string
    {
        return '';
    }
}
