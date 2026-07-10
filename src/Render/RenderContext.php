<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

use DOMDocument;
use DOMElement;
use DOMException;
use MaxSem\Hiero\ErrorHandler;
use MaxSem\Hiero\Font;
use MaxSem\Hiero\HieroException;

final class RenderContext
{
    public readonly ErrorHandler $errors;

    public readonly DOMDocument $dom;

    /**
     * @var array<string, DOMElement>
     */
    private array $glyphCache = [];

    public function __construct(
        public readonly RenderOptions $options,
        public readonly Font $font,
    ) {
        $this->errors = new ErrorHandler($this->options, RenderException::class);
        $this->dom = new DOMDocument();
    }

    public function getGlyph(string $gardinerCode): DOMElement
    {
        if (!isset($this->glyphCache[$gardinerCode])) {
            $doc = new DOMDocument();
            if (!$doc->loadXML($this->font->getSvg($gardinerCode))) {
                throw new HieroException("Error parsing XML for glyph $gardinerCode");
            }

            $this->glyphCache[$gardinerCode] = $doc->documentElement
                ?? throw new HieroException("documentElement is empty for glyph $gardinerCode");
        }

        /** @var DOMElement $glyph */
        $glyph = $this->dom->importNode($this->glyphCache[$gardinerCode], true);

        return $glyph;
    }

    public function createSvgElement(): DOMElement
    {
        return $this->createElement('svg');
    }

    public function createElement(string $name): DOMElement
    {
        return $this->dom->createElementNS('http://www.w3.org/2000/svg', $name);
    }
}
