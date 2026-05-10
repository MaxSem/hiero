<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

use MaxSem\Hiero\HieroException;
use MaxSem\Hiero\Render\RenderBox;
use MaxSem\Hiero\Render\RenderContext;

final readonly class Subdivision extends Container
{
    public function separator(): string
    {
        return ':';
    }

    public function markup(): string
    {
        return $this->innerMarkup();
    }

    public function render(RenderContext $context): RenderBox
    {
        throw new HieroException('Not implemented');
    }
}
