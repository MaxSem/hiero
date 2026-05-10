<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

use MaxSem\Hiero\HieroException;
use MaxSem\Hiero\Render\RenderBox;
use MaxSem\Hiero\Render\RenderContext;

final readonly class VerbatimText extends Block
{
    public function __construct(
        public string $content,
    ) {
    }

    public function markup(): string
    {
        return "+l {$this->content} +s";
    }

    public function render(RenderContext $context): RenderBox
    {
        throw new HieroException('Not implemented');
    }
}
