<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

class Hieroglyph
{
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
