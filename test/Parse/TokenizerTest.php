<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Test;

use MaxSem\Hiero\Parse\Tokenizer;
use PHPUnit\Framework\TestCase;

class TokenizerTest extends TestCase
{
    /**
     * @dataProvider provideTokenize
     */
    public function testTokenize(string $input, array $expected): void
    {
        $t = new Tokenizer();

        $result = $t->tokenize($input);

        self::assertSame($expected, $result);
    }

    public static function provideTokenize(): array
    {
        return [
            [ '', [] ],
            [ '', [] ],
            [ ' ', [] ],
            [ ' ', [] ],

            [ 'A1', ['A1'] ],
            [ 'A1', ['A1'] ],

            [ 'A1 B1', ['A1', 'B1'] ],
            [ 'A1-B1', ['A1', 'B1'] ],
            [ 'A1 - B1', ['A1', 'B1'] ],
            [ 'A1 -- B1', ['A1', 'B1'] ],
            [ 'A1:B1', ['A1', ':', 'B1'] ],
            [ "A1-!\r\nB1", ['A1', '!', 'B1'] ],
            [ "A1!\nB1", ['A1', '!', 'B1'] ],
            [ "A1 ! B1", ['A1', '!', 'B1'] ],
        ];
    }
}
