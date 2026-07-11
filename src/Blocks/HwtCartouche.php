<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

use MaxSem\Hiero\Render\RenderBox;
use MaxSem\Hiero\Render\RenderContext;
use MaxSem\Hiero\ViewBox;

final readonly class HwtCartouche extends BoundedBlock
{
    private const START_LOW_BOX = '<h2';
    private const START_HIGH_BOX = '<h3';

    private const END_LOW_BOX = 'h2>';
    private const END_HIGH_BOX = 'h3>';

    public function render(RenderContext $context): RenderBox
    {
        $contents = $this->renderHorizontalBlock($context, $this->innerBlocks);

        // Build a path https://developer.mozilla.org/en-US/docs/Web/SVG/Reference/Attribute/d

        // Basic dimensions
        $lineWidth = intval($context->font->defaultSize->height / 18); // @todo: something better?
        $margin = $halfStroke = intval($lineWidth / 2);
        $cartoucheHeight = $contents->viewBox->height + $lineWidth * 2 + $margin * 2;
        $sideWidth = intval($cartoucheHeight / 2);
        $boxSide = intval($cartoucheHeight / 3);
        $cartoucheWidth = $sideWidth * 2 + $contents->viewBox->width;

        $startX = $halfStroke;
        $startY = $halfStroke;
        $endX = $cartoucheWidth - $halfStroke;
        $endY = $cartoucheHeight - $halfStroke;

        // MoveTo
        $path = ["M $startX $startY"];

        // Horizontal line
        $path[] = "H $endX";
        // Vertical line
        $path[] = "V $endY";
        // Horizontal line
        $path[] = "H $startX";
        // Close path
        $path[] = 'z';

        if ($this->opener === self::START_LOW_BOX) {
            $startX = 0;
            $startY = $cartoucheHeight - $boxSide - $halfStroke;
            $endX = $boxSide - $halfStroke;
            $endY = $cartoucheHeight;

            $path[] = "M $startX $startY";
            $path[] = "H $endX";
            $path[] = "V $endY";
        }

        if ($this->opener === self::START_HIGH_BOX) {
            $startX = 0;
            $startY = $boxSide - $halfStroke;
            $endX = $boxSide - $halfStroke;
            $endY = 0;

            $path[] = "M $startX $startY";
            $path[] = "H $endX";
            $path[] = "V $endY";
        }

        if ($this->closer === self::END_LOW_BOX) {
            $startX = $cartoucheWidth - $halfStroke;
            $startY = $cartoucheHeight - $boxSide - $halfStroke;
            $endX = $cartoucheWidth - $boxSide - $halfStroke;
            $endY = $cartoucheHeight;

            $path[] = "M $startX $startY";
            $path[] = "H $endX";
            $path[] = "V $endY";
        }

        if ($this->closer === self::END_HIGH_BOX) {
            $startX = $cartoucheWidth - $halfStroke;
            $startY = $boxSide - $halfStroke;
            $endX = $cartoucheWidth - $boxSide - $halfStroke;
            $endY = 0;

            $path[] = "M $startX $startY";
            $path[] = "H $endX";
            $path[] = "V $endY";
        }

        $path = implode(' ', $path);

        // Create path element
        $svg = $context->createSvgElement();
        $svg->setAttribute('class', 'cartouche hwt-cartouche');
        $pathElement = $context->createElement('path');
        $pathElement->setAttribute('stroke', 'currentColor');
        $pathElement->setAttribute('stroke-width', (string)$lineWidth);
        $pathElement->setAttribute('fill', 'none');
        $pathElement->setAttribute('d', $path);
        $svg->appendChild($pathElement);

        // Place cartouche contents
        $inner = $contents->output;
        $innerX = $sideWidth;
        $innerY = $lineWidth + $margin;
        $inner->setAttribute('x', (string)$innerX);
        $inner->setAttribute('y', (string)$innerY);
        $svg->appendChild($inner);

        $viewBox = new ViewBox(0, 0, $cartoucheWidth, $cartoucheHeight);
        $svg->setAttribute('viewBox', $viewBox->toString());

        return new RenderBox($svg, $viewBox);
    }
}
