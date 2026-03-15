<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

final readonly class Document extends Container
{
    public function separator(): string
    {
        return "\n";
    }

    public function markup(): string
    {
        return $this->innerMarkup();
    }
}
