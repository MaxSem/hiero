<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

class EmptyBlock extends Block
{
    public function markup(): string
    {
        return '';
    }
}
