<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

final class Unicode
{
    /** @var array<int, string> */
    private static array $charToGardiner = [];
    /** @var array <string, int> */
    private static array $gardinerToChar = [];
    /** @var array<string, string[]> */
    private static array $categories = [];

    /**
     * @return array<int, string>
     */
    public static function charToGardiner(): array
    {
        if (!self::$charToGardiner) {
            self::loadUnicode();
        }

        return self::$charToGardiner;
    }

    /**
     * @return array<string, int>
     */
    public static function gardinerToChar(): array
    {
        if (!self::$gardinerToChar) {
            self::loadUnicode();
        }

        return self::$gardinerToChar;
    }

    /**
     * @return array<string, string[]>
     */
    public static function categories(): array
    {
        if (!self::$categories) {
            self::loadUnicode();
        }

        return self::$categories;
    }

    private static function loadUnicode(): void
    {
        $path = __DIR__ . '/../data/unicode.php';

        [
            'charToGardiner' => self::$charToGardiner,
            'gardinerToChar' => self::$gardinerToChar,
            'categories' => self::$categories,
        ] = require $path;
    }
}
