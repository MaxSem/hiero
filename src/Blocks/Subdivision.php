<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

use MaxSem\Hiero\Render\RenderBox;
use MaxSem\Hiero\Render\RenderContext;
use MaxSem\Hiero\ViewBox;

final readonly class Subdivision extends Container
{
    public function separator(): string
    {
        return ':';
    }

    public function markup(): string
    {
        return $this->innerMarkup();
    }

    public function render(RenderContext $context): RenderBox
    {
        $rendered = array_map(fn (Block $b) => $b->render($context), $this->innerBlocks);
        $viewBoxes = array_map(fn (RenderBox $rb) => $rb->viewBox, $rendered);
        $maxWidth = ViewBox::maxWidth($viewBoxes);

        $svg = $context->createSvgElement();
        $svg->setAttribute('class', 'subdivision');

        $height = 0;
        foreach ($rendered as $block) {
            $x = ($maxWidth - $block->viewBox->width) / 2;
            $block->output->setAttribute('x', (string)$x);
            $block->output->setAttribute('y', (string)$height);

            $svg->appendChild($block->output);
            $height += $block->viewBox->height;
        }

        $scale = 1.0 / count($this->innerBlocks);

        $innerBox = new ViewBox(0, 0, $maxWidth, $height);
        $outerBox = $innerBox->scale($scale);

        $svg->setAttribute('viewBox', $innerBox->toString());
        $svg->setAttribute('width', (string)$outerBox->width);
        $svg->setAttribute('height', (string)$outerBox->height);

        return new RenderBox($svg, $outerBox);
    }
}
