<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

class Options
{
    public function __construct(
        public readonly float $lineSpacing = 10.0,
        public readonly string $svgDir = __DIR__ . '/../../data/svg',
        public readonly ?string $svgFontAttribution = null,
        public readonly string $unicodeFontFamily = "'Egyptian Text', 'Noto Sans Egyptian Hieroglyphs'",
    ) {
    }
}
