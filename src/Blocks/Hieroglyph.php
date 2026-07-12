<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

use MaxSem\Hiero\ErrorCodes;
use MaxSem\Hiero\HieroException;
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

        if (!$box) {
            return $this->missingGlyph($context);
        }

        $svg = $context->getGlyph($this->code);
        $svg->removeAttribute('viewBox');
        $desc = $this->code . ($this->phonetic === null ? '' : " [{$this->phonetic}]");
        $unicode = mb_chr((int)Unicode::gardinerToCodePoint($this->code));
        $svg->setAttribute('data-gardiner', $desc);
        $svg->setAttribute('data-text', $unicode);
        $svg->setAttribute('class', 'glyph');

        $transformations = [];
        $rotation = $this->modifiers->rotation;
        $origWidth = $box->width;
        $origHeight = $box->height;

        switch ($rotation) {
            case 0:
                break;
            case 90:
                $transformations[] = "translate($origHeight, 0) rotate(90)";
                $box = $box->rotate90deg();
                break;
            case 180:
                $transformations[] = "translate($origWidth, $origHeight) rotate(180)";
                break;
            case 270:
                $transformations[] = "translate(0, $origWidth) rotate(270)";
                $box = $box->rotate90deg();
                break;
            default:
                $transformations[] = "rotate($rotation)";
        }

        if ($this->modifiers->mirror) {
            $transformations[] = "translate($origWidth, 0) scale(-1, 1)";
        }

        if ($transformations) {
            $group = $svg->firstElementChild;
            if (!$group || $group->nodeName !== 'g') {
                throw new HieroException("Unexpected SVG structure for glyph {$this->code}");
            }
            $group->setAttribute('transform', implode(' ', $transformations));
        }

        return new RenderBox($svg, $box);
    }

    private function missingGlyph(RenderContext $context): RenderBox
    {
        $context->errors->add(ErrorCodes::FONT_MISSING_GLYPH, $this->code);
        $svg = $context->createSvgElement();
        $box = $context->font->defaultSize;

        return new RenderBox($svg, $box);
    }
}
