<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

use MaxSem\Hiero\HieroException;
use MaxSem\Hiero\Render\RenderBox;
use MaxSem\Hiero\Render\RenderContext;

final readonly class Parentheses extends BoundedBlock
{
    public function render(RenderContext $context): RenderBox
    {
        return $this->renderHorizontalBlock($context, $this->innerBlocks);
    }
}
