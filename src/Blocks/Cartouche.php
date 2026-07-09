<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

use MaxSem\Hiero\Render\RenderBox;
use MaxSem\Hiero\Render\RenderContext;
use MaxSem\Hiero\ViewBox;

final readonly class Cartouche extends BoundedBlock
{
    private const REVERSE_START = '<2';
    private const NORMAL_END = ['>', '2>'];

    public function render(RenderContext $context): RenderBox
    {
        $contents = $this->renderHorizontalBlock($context, $this->innerBlocks);

        // Build a path https://developer.mozilla.org/en-US/docs/Web/SVG/Reference/Attribute/d

        // Basic dimensions
        $lineWidth = intval($context->font->defaultSize->height / 18); // @todo: something better?
        $margin = $halfStroke = intval($lineWidth / 2);
        $cartoucheHeight = $contents->viewBox->height + $lineWidth * 2 + $margin * 2;
        $sideWidth = intval($cartoucheHeight / 3);
        $cartoucheWidth = $sideWidth * 2 + $contents->viewBox->width;

        // MoveTo
        $startX = $sideWidth;
        $startY = $halfStroke;
        $path = ["M $startX $startY"];

        // Left arc
        $endX = $startX;
        $endY = $cartoucheHeight - $halfStroke;
        $rx = $sideWidth - $halfStroke;
        $ry = $cartoucheHeight / 2 - $halfStroke;
        $path[] = "A $rx $ry 0 0 0 $endX $endY";

        // Horizontal line
        $path[] = "h {$contents->viewBox->width}";

        // Right arc
        $endX = $startX + $contents->viewBox->width;
        $endY = $startY;
        $path[] = "A $rx $ry 0 0 0 $endX $endY";

        // Close path
        $path[] = 'z';

        // Close to the left
        if ($this->opener === self::REVERSE_START) {
            $startX = $halfStroke;
            $startY = 0;
            $path[] = "M $startX $startY";
            $path[] = "v $cartoucheHeight";
        }

        // Close to the right
        if (in_array($this->closer, self::NORMAL_END, true)) {
            $startX = $cartoucheWidth - $halfStroke;
            $startY = 0;
            $path[] = "M $startX $startY";
            $path[] = "v $cartoucheHeight";
        }

        $path = implode(' ', $path);

        // Create path element
        $svg = $context->createSvgElement();
        $svg->setAttribute('class', 'hiero-cartouche');
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
