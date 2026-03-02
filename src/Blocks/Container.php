<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

abstract readonly class Container extends Block
{
    public function __construct(
        /** @var Block[] */
        public array $innerBlocks,
    ) {
    }

    public function separator(): string
    {
        return '-';
    }

    protected function innerMarkup(): string
    {
        $markup = array_map(fn (Block $b) => $b->markup(), $this->innerBlocks);

        return implode($this->separator(), $markup);
    }
}
