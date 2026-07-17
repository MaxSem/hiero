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
        $box = $context->font->defaultSize;

        $fontSize = 100;
        $charWidth = 60;
        $textWidth = $charWidth * max(1, mb_strlen($this->content));

        $svg = $context->createSvgElement();
        $svg->setAttribute('viewBox', "0 0 $textWidth $fontSize");
        $svg->setAttribute('preserveAspectRatio', 'xMidYMid');
        $svg->setAttribute('width', (string)$box->width);
        $svg->setAttribute('height', (string)$box->height);

        $text = $context->createElement('text');
        $text->setAttribute('x', '0');
        $text->setAttribute('y', (string)(int)($fontSize * 0.85));
        $text->setAttribute('font-size', (string)$fontSize);
        $text->setAttribute('font-family', 'sans-serif');
        $text->textContent = $this->content;

        $svg->appendChild($text);

        return new RenderBox($svg, $box);
    }
}
