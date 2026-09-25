<?php

declare(strict_types=1);

namespace Tests\Hiero\Render;

use MaxSem\Hiero\Render\Ink;
use PHPUnit\Framework\TestCase;

class InkTest extends TestCase
{
    public function testCombineWith(): void
    {
        $a = new Ink(0, 0, 10, 10);
        $b = new Ink(5, 5, 10, 20);

        $result = $a->combineWith($b, 10, 0);
        $expected = new Ink(0, 0, 25, 25);

        self::assertEquals($expected, $result);
    }

    public function testTotalHeight(): void
    {
        $input = [
            new Ink(0, 0, 10, 10),
            new Ink(5, 5, 10, 20),
            new Ink(0, 0, 25, 25),
        ];

        self::assertSame(55.0, Ink::totalHeight($input));
    }
}
