<?php

declare(strict_types=1);

namespace Tests\Hiero\Parse;

use MaxSem\Hiero\Parse\MaxTokensException;
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

            [ 'A1 B1', ['A1', '-', 'B1'] ],
            [ 'A1  B1', ['A1', '-', 'B1'] ],
            [ 'A1-B1', ['A1', '-', 'B1'] ],
            [ 'A1 - B1', ['A1','-', 'B1'] ],
            [ 'A1 -- B1', ['A1', '-', 'B1'] ],
            [ 'A1:B1', ['A1', ':', 'B1'] ],
            [ "A1-!\r\nB1", ['A1', '-', '!', 'B1'] ],
            [ "A1 !\nB1", ['A1', '!', 'B1'] ],
            [ "A1 ! B1", ['A1', '!', 'B1'] ],
            [ "A1!B1", ['A1', '!', 'B1'] ],
            [ "A1-!-B1", ['A1', '-', '!', '-', 'B1'] ],

            [ 'A1.B1', ['A1', '-', '.', '-', 'B1'] ],
            [ 'A1..B1', ['A1', '-', '..', '-', 'B1'] ],
            [ 'A1-.-B1', ['A1', '-', '.', '-', 'B1'] ],
            [ 'A1-..-B1', ['A1', '-', '..', '-', 'B1'] ],

            [ 'A1-(B1-C1)', [ 'A1', '-', '(', 'B1', '-', 'C1', ')'] ],
            [ 'A1:(B1-C1)', [ 'A1', ':', '(', 'B1', '-', 'C1', ')'] ],
            [ 'A1*(B1-C1)', [ 'A1', '*', '(', 'B1', '-', 'C1', ')'] ],
            [ 'A1:(B1-C1):D1', [ 'A1', ':', '(', 'B1', '-', 'C1', ')', ':', 'D1'] ],
        ];
    }

    public function testMaxTokens(): void
    {
        $t = new Tokenizer(5);

        // No exception
        $t->tokenize('A1-B1-C1');

        self::expectException(MaxTokensException::class);
        $t->tokenize('A1-B1-C1-');
    }
}
