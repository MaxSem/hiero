<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

use MaxSem\Hiero\HieroglyphModifiers;

final readonly class Hieroglyph extends Block
{
    public function __construct(
        public string $code,
        public HieroglyphModifiers $modifiers,
    ) {
    }

    public function markup(): string
    {
        return $this->code . $this->modifiers->markup;
    }
}
