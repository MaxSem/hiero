<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

use DOMDocument;
use DOMElement;
use MaxSem\Hiero\ErrorHandler;
use MaxSem\Hiero\Font;
use MaxSem\Hiero\HieroException;

final class RenderContext
{
    public readonly ErrorHandler $errors;

    public readonly DOMDocument $dom;

    /**
     * @var array<string, string>
     */
    private array $glyphsEncountered = [];

    public function __construct(
        public readonly RenderOptions $options,
        public readonly Font $font,
    ) {
        $this->errors = new ErrorHandler($this->options, RenderException::class);
        $this->dom = new DOMDocument();
    }

    public function getGlyph(string $gardinerCode): DOMElement
    {
        $viewBox = $this->glyphsEncountered[$gardinerCode] ?? null;

        if ($viewBox === null) {
            $svg = $this->loadGlyph($gardinerCode);
            $viewBox = $svg->getAttribute('viewBox');
            if (!$viewBox) {
                throw new HieroException("SVG for $gardinerCode has no viewBox attribute");
            }
            $this->glyphsEncountered[$gardinerCode] = $viewBox;
        } else {
            $glyph = $this->createElement('use');
            $glyph->setAttribute('href', "#$gardinerCode");
            $group = $this->createElement('g');
            $group->appendChild($glyph);
            $svg = $this->createSvgElement();
            $svg->appendChild($group);
            $svg->setAttribute('viewBox', $viewBox);
        }

        return $svg;
    }

    private function loadGlyph(string $gardinerCode): DOMElement
    {
        $doc = new DOMDocument();
        if (!$doc->loadXML($this->font->getSvg($gardinerCode))) {
            throw new HieroException("Error parsing XML for glyph $gardinerCode");
        }

        $root = $doc->documentElement ?? throw new HieroException("documentElement is empty for glyph $gardinerCode");

        return $this->dom->importNode($root, true); // @phpstan-ignore return.type
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
