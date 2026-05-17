<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

use MaxSem\Hiero\Render\RenderBox;
use MaxSem\Hiero\Render\RenderContext;
use MaxSem\Hiero\ViewBox;

final readonly class VoidBlock extends Block
{
    public const HALF_WIDTH = 1;
    public const FULL_WIDTH = 2;

    public function __construct(
        public int $width,
    ) {
    }

    public function markup(): string
    {
        return str_repeat('.', $this->width);
    }

    public function render(RenderContext $context): RenderBox
    {
        $box = new ViewBox(
            0,
            0,
            ($context->font->defaultSize->width * $this->width) / 2,
            $context->font->defaultSize->height,
        );

        $void = $context->createSvgElement();
        $void->setAttribute('class', 'hiero-void');
        $void->setAttribute('data-gardiner', $this->markup());
        $void->setAttribute('width', (string)$box->width);
        $void->setAttribute('height', (string)$box->height);

        return new RenderBox($void, $box);
    }
}
