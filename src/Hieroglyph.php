<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

class Hieroglyph
{
    public function __construct(
        private string $gardinerCode,
        private string $modifiers,
    ) {
    }
    

    public static function normalize(string $maybeHieroglyph): string
    {
        return mb_ucfirst(mb_strtolower($maybeHieroglyph));
    }

    public static function isValid(string $normalizedString): bool
    {
        static $gardinerToChar = Unicode::gardinerToChar();

        return isset($gardinerToChar[$normalizedString]);
    }

    public static function parse(string $maybeHieroglyph): ?self
    {
        $regexp = '/^((?:AA|[A-Z])\d{3}[A-Z]?|[A-Z]+)(?:\\\\w*)?$/i';

        if (!preg_match($regexp, $maybeHieroglyph, $matches)) {
            return null;
        }
    }
}
