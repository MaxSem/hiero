<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

use MaxSem\Hiero\Render\RenderBox;
use MaxSem\Hiero\Render\RenderContext;
use MaxSem\Hiero\ViewBox;

final readonly class Document extends Container
{
    public function separator(): string
    {
        return "-!\n";
    }

    public function markup(): string
    {
        return $this->innerMarkup();
    }

    public function render(RenderContext $context): RenderBox
    {
        $rendered = array_map(fn (Block $b) => $b->render($context), $this->innerBlocks);
        $viewBoxes = array_map(fn (RenderBox $b) => $b->viewBox, $rendered);
        $maxWidth = ViewBox::maxWidth($viewBoxes);

        $doc = $context->createSvgElement();
        $y = 0;
        foreach ($rendered as $renderBox) {
            $box = $renderBox->viewBox->shift(0, $y);
            $renderBox->output->setAttribute('viewBox', $box->toString());
            $doc->appendChild($renderBox->output);
            $y += $box->height;
        }

        $resultingBox = new ViewBox(0, 0, $maxWidth, $y);
        $doc->setAttribute('viewBox', $resultingBox->toString());
        $doc->className = 'hiero-document';

        return new RenderBox($doc, $resultingBox);
    }
}
