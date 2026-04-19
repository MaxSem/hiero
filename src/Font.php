<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

class Font
{
    public function __construct(
        /** Directory where font .svg files and _font.php metadata are located */
        readonly string $path,
    ) {
    }
}
