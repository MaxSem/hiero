<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

use MaxSem\Hiero\Render\RenderBox;
use MaxSem\Hiero\Render\RenderContext;

abstract readonly class Block
{
    abstract public function markup(): string;

    abstract public function render(RenderContext $context): RenderBox;
}
