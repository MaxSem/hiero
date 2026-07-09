<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

use MaxSem\Hiero\Render\RenderBox;
use MaxSem\Hiero\Render\RenderContext;

final readonly class Line extends Container
{
    public function markup(): string
    {
        return $this->innerMarkup();
    }

    public function render(RenderContext $context): RenderBox
    {
        $result = $this->renderHorizontalBlock($context, $this->innerBlocks);
        $result->output->setAttribute('class', 'line');

        return $result;
    }
}
