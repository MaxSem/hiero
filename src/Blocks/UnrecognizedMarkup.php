<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

use MaxSem\Hiero\Render\RenderBox;
use MaxSem\Hiero\Render\RenderContext;

final readonly class UnrecognizedMarkup extends Block
{
    public function __construct(
        public string $content,
    ) {
    }

    public function markup(): string
    {
        return $this->content;
    }

    public function render(RenderContext $context): RenderBox
    {
        $svg = $context->createSvgElement();
        $box = $context->font->defaultSize;

        $svg->setAttribute('viewBox', $box->toString());

        $text = $context->createElement('text');
        $text->setAttribute('x', '20%');
        $text->setAttribute('y', '50%');
        $text->setAttribute('font-size', '30');
        $text->setAttribute('lengthAdjust', 'spacingAndGlyphs');
        $text->textContent = $this->content;

        $svg->appendChild($text);

        return new RenderBox($svg, $box);
    }
}
