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
        $doc = $context->createSvgElement();
        $doc->setAttribute('class', 'document');

        if ($context->options->color !== null) {
            $doc->setAttribute('color', $context->options->color);
        }

        if ($context->options->background !== null) {
            $doc->setAttribute('style', "background: {$context->options->background}");
        }

        if ($context->options->style !== null) {
            $style = $context->createElement('style');
            $style->textContent = $context->options->style;
            $doc->appendChild($style);
        }

        $rendered = array_map(fn (Block $b) => $b->render($context), $this->innerBlocks);
        $viewBoxes = array_map(fn (RenderBox $b) => $b->viewBox, $rendered);
        $maxWidth = ViewBox::maxWidth($viewBoxes);

        $y = 0;
        foreach ($rendered as $renderBox) {
            $box = $renderBox->viewBox->shift(0, $y);
            $renderBox->output->setAttribute('x', (string)$box->minX);
            $renderBox->output->setAttribute('y', (string)$box->minY);

            $doc->appendChild($renderBox->output);
            $y += $box->height;
        }

        $resultingBox = new ViewBox(0, 0, $maxWidth, $y);
        $doc->setAttribute('viewBox', $resultingBox->toString());

        return new RenderBox($doc, $resultingBox);
    }
}
