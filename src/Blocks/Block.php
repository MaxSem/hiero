<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

use MaxSem\Hiero\Render\RenderBox;
use MaxSem\Hiero\Render\RenderContext;

/**
 * Base block class. Blocks serve as both AST nodes for a parsed document and as renderers.
 */
abstract readonly class Block
{
    /**
     * Returns MdC markup for this block
     */
    abstract public function markup(): string;

    /**
     * Renders a block into an SVG.
     */
    abstract public function render(RenderContext $context): RenderBox;
}
