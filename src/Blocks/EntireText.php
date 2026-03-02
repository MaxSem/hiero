<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

final readonly class EntireText extends Container
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