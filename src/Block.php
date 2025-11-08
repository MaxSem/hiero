<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

use MaxSem\Hiero\Render\Renderer;
use MaxSem\Hiero\Render\Size;
use MaxSem\Hiero\Render\Position;

abstract class Block
{
    public abstract function size(): Size;
    public abstract function markup(): string;
    public abstract function render(Renderer $renderer, Position $position): Size;
}