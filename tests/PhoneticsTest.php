<?php

declare(strict_types=1);

namespace Tests\Hiero;

use MaxSem\Hiero\Phonetics;
use PHPUnit\Framework\TestCase;

class PhoneticsTest extends TestCase
{
    /**
     * @dataProvider provideNormalize
     */
    public function testNormalize(string $input, ?string $expected): void
    {
        self::assertSame($expected, Phonetics::normalize($input));
    }

    public static function provideNormalize(): array
    {
        return [
            ['', null],
            ['test', null],
            ['тест', null],
            ['p', 'p'],
            ['P', 'p'],
            ['msa', 'mSa'],
            ['x', 'x'],
            ['X', 'X'],
            ['DbA', 'DbA'],
            ['dba', 'DbA'],
        ];
    }

    /**
     * @dataProvider provideTranslate
     */
    public function testTranslate(string $input, ?string $expected): void
    {
        self::assertSame($expected, Phonetics::translateToGardiner($input));
    }

    public static function provideTranslate(): array
    {
        return [
            ['', null],
            ['test', null],
            ['тест', null],
            ['x', 'J1'],
            ['X', 'F32'],
            ['iry', 'A47'],
            ['IRy', 'A47'],
        ];
    }
}
