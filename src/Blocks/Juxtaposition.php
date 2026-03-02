<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

final readonly class Juxtaposition extends Container
{
    public function separator(): string
    {
        return '*';
    }

    public function markup(): string
    {
        return $this->innerMarkup();
    }
}