<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

abstract readonly class BoundedBlock extends Container
{
    public function __construct(
        public string $opener,
        /** @var Block[] */
        public array $innerBlocks,
        public string $closer,
    ) {
        parent::__construct($innerBlocks);
    }

    public function markup(): string
    {
        return $this->opener . $this->innerMarkup() . $this->closer;
    }
}