<?php

declare(strict_types=1);

namespace Tests\Hiero\Build;

use DOMDocument;
use Exception;
use MaxSem\Hiero\Build\PathParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PathParserTest extends TestCase
{
    public function testNoInkPreParse(): void
    {
        $parser = new PathParser();
        self::assertNull($parser->getInk());
    }

    public function testParseSvgThrows(): void
    {
        $parser = new PathParser();
        $doc = new DOMDocument();
        $doc->appendChild($doc->createElement('path'));

        $this->expectException(Exception::class);
        $parser->parseSvg($doc);
    }

    #[DataProvider('providePaths')]
    public function testParsePath(string $path, ?array $expected): void
    {
        $parser = new PathParser();
        $parser->parsePath($path);

        $ink = $parser->getInk();

        if (!$expected) {
            self::assertNull($ink);
        } else {
            self::assertEquals($expected, $ink->toArray());
        }
    }

    #[DataProvider('providePaths')]
    public function testParseSvg(string $path, ?array $expected): void
    {
        $doc = new DOMDocument();
        $pathElement = $doc->createElement('path');
        $pathElement->setAttribute('d', $path);
        $doc->appendChild($pathElement);

        $parser = new PathParser();
        $parser->parseSvg($doc);

        $ink = $parser->getInk();

        if (!$expected) {
            self::assertNull($ink);
        } else {
            self::assertEquals($expected, $ink->toArray());
        }
    }

    public static function providePaths(): iterable
    {
        return [
            ['', null],
            ['M0 0', [0, 0, 0, 0]],
            ['M 0 0 h 100 v 100 h -100 z', [0, 0, 100, 100]],
            ['M244 886q0 49 -38 81.5t-88 32.5q-48 0 -83 -34t-35 -82q0 -50 35 -87t85 -37q51 0 87.5 37.5t36.5 88.5zM120 800q-33 0 -56.5 24.5t-23.5 59.5q0 31 23 53.5t55 22.5q35 0 60.5 -21.5t25.5 -52.5q0 -35 -25 -60.5t-59 -25.5z', [0, 760, 244, 240]] // phpcs:ignore
        ];
    }
}
