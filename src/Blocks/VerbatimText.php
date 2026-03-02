<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

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
}
