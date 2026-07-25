<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

use MaxSem\Hiero\Blocks\Document;
use MaxSem\Hiero\Error;
use MaxSem\Hiero\Font;
use MaxSem\Hiero\Options;

readonly class Renderer
{
    public function __construct(
        public Options $options,
        public Font $font,
    ) {
    }

    /**
     * @param Error[] $parseErrors
     */
    public function render(Document $document, array $parseErrors = []): RenderOutput
    {
        $context = new RenderContext($this->options, $this->font);
        if ($parseErrors) {
            $context->errors->mergeErrors($parseErrors);
        }

        $box = $document->render($context);

        $context->dom->appendChild($box->output);
        $xml = (string)$context->dom->saveXML();

        return new RenderOutput($xml, $box->viewBox, $context->errors->get());
    }
}
