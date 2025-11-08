<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

class Renderer
{
    public function __construct(
        public readonly Mode $mode,
        public readonly Options $options,
    ) {
    }
}