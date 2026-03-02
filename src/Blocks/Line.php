<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

final readonly class Line extends Container
{
    public function markup(): string
    {
        return $this->innerMarkup() . '-!';
    }
}
