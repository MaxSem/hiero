<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

/**
 * Base for all container blocks that have open and close markers, e.g. parentheses.
 */
abstract readonly class BoundedBlock extends Container
{
    /**
     * @param Block[] $innerBlocks
     */
    public function __construct(
        public string $opener,
        array $innerBlocks,
        public string $closer,
    ) {
        parent::__construct($innerBlocks);
    }

    public function markup(): string
    {
        return $this->opener . $this->innerMarkup() . $this->closer;
    }
}
