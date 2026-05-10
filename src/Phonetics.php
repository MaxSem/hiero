<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

final class Phonetics
{
    /** @var array<string, string> */
    private static array $gardinerToPhonetic = [];

    /** @var array<string, string> */
    private static array $phoneticToGardiner = [];

    /** @var array<string, string> */
    private static array $lowerCaseIndex = [];

    public static function normalize(string $phonetic): ?string
    {
        if (!self::$lowerCaseIndex) {
            self::load();
        }

        if (isset(self::$phoneticToGardiner[$phonetic])) {
            return $phonetic;
        }

        $phonetic = strtolower($phonetic);

        return self::$lowerCaseIndex[$phonetic] ?? null;
    }

    public static function translateToGardiner(string $phonetic): ?string
    {
        $phonetic = self::normalize($phonetic);

        return self::$phoneticToGardiner[$phonetic] ?? null;
    }

    /**
     * @return array<string, string>
     */
    public static function gardinerToPhonetic(): array
    {
        if (!self::$gardinerToPhonetic) {
            self::load();
        }

        return self::$gardinerToPhonetic;
    }

    /**
     * @return array<string, string>
     */
    public static function phoneticToGardiner(): array
    {
        if (!self::$phoneticToGardiner) {
            self::load();
        }

        return self::$phoneticToGardiner;
    }

    /**
     * @return array<string, string>
     */
    public static function lowerCaseIndex(): array
    {
        if (!self::$lowerCaseIndex) {
            self::load();
        }

        return self::$lowerCaseIndex;
    }

    private static function load(): void
    {
        $path = __DIR__ . '/../data/phonetics.php';

        [
            'gardinerToPhonetic' => self::$gardinerToPhonetic,
            'phoneticToGardiner' => self::$phoneticToGardiner,
            'lowerCaseIndex' => self::$lowerCaseIndex,
        ] = require $path;
    }
}
