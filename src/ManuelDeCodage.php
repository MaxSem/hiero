<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

use MaxSem\Hiero\Parse\Parser;
use MaxSem\Hiero\Parse\Tokenizer;
use MaxSem\Hiero\Render\Renderer;
use MaxSem\Hiero\Render\RenderOutput;

/**
 * Façade that takes care of everything
 */
class ManuelDeCodage
{
    public function __construct(
        private readonly Options $options,
        private readonly Font $font,
    ) {
    }

    public function parseAndRender(string $markup): RenderOutput
    {
        $tokenizer = new Tokenizer($this->options->maxTokens);
        $parser = new Parser($tokenizer, $this->options);
        $parseOutput = $parser->parse($markup);

        $renderer = new Renderer($this->options, $this->font);
        return $renderer->render($parseOutput->result, $parseOutput->errors);
    }
}
