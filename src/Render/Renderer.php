<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

use MaxSem\Hiero\Blocks\Document;
use MaxSem\Hiero\Font;

readonly class Renderer
{
    public function __construct(
        public RenderOptions $options,
        public Font $font,
    ) {
    }

    public function render(Document $document): RenderOutput
    {
        $context = new RenderContext($this->options, $this->font);

        $box = $document->render($context);

        $context->dom->appendChild($box->output);
        $xml = (string)$context->dom->saveXML();

        return new RenderOutput($xml, $box->viewBox, $context->errors->get());
    }
}
