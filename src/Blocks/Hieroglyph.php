<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

use MaxSem\Hiero\ErrorCodes;
use MaxSem\Hiero\HieroglyphModifiers;
use MaxSem\Hiero\Render\RenderBox;
use MaxSem\Hiero\Render\RenderContext;
use MaxSem\Hiero\Unicode;

final readonly class Hieroglyph extends Block
{
    public function __construct(
        public string $code,
        public ?string $phonetic,
        public HieroglyphModifiers $modifiers,
        public ?string $originalMarkup = null,
    ) {
    }

    public function markup(): string
    {
        return ($this->phonetic ?? $this->code) . $this->modifiers->markup;
    }

    public function render(RenderContext $context): RenderBox
    {
        $box = $context->font->getViewBox($this->code);

        if ($box) {
            $svg = $context->getGlyph($this->code);
            $svg->removeAttribute('viewBox');
            $desc = $this->code . ($this->phonetic === null ? '' : " [{$this->phonetic}]");
            $unicode = mb_chr((int)Unicode::gardinerToCodePoint($this->code));
            $svg->setAttribute('data-gardiner', $desc);
            $svg->setAttribute('data-text', $unicode);
        } else {
            $context->errors->add(ErrorCodes::FONT_MISSING_GLYPH, $this->code);
            $svg = $context->createSvgElement();
            $box = $context->font->defaultSize;
        }

        $svg->setAttribute('class', 'glyph');

        // @todo: modifiers

        return new RenderBox($svg, $box);
    }
}
