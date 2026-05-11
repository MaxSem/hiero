<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

use MaxSem\Hiero\Render\RenderBox;
use MaxSem\Hiero\Render\RenderContext;
use MaxSem\Hiero\ViewBox;

/**
 * Base class for all blocks that contain other blocks.
 */
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

    /**
     * @param Block[] $blocks
     */
    protected function renderHorizontalBlock(RenderContext $context, array $blocks, int $x = 0, int $y = 0): RenderBox
    {
        $rendered = array_map(fn (Block $b) => $b->render($context), $blocks);
        $viewBoxes = array_map(fn (RenderBox $b) => $b->viewBox, $rendered);
        $maxHeight = ViewBox::maxHeight($viewBoxes);

        $line = $context->createGroupElement();
        $curX = $x;
        foreach ($rendered as $renderBox) {
            $box = $renderBox->viewBox->shift($curX, $y + $maxHeight - $renderBox->viewBox->height);
            $renderBox->output->removeAttribute('viewBox');
            $renderBox->output->setAttribute('x', (string)$curX);
            $renderBox->output->setAttribute('y', (string)$y);
            $line->appendChild($renderBox->output);
            $curX += $box->width;
        }

        $resultingBox = new ViewBox($x, $y, $curX, $maxHeight);
        $line->setAttribute('viewBox', $resultingBox->toString());

        return new RenderBox($line, $resultingBox);
    }
}
