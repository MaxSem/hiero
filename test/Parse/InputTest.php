<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Test;

use MaxSem\Hiero\Blocks\Cartouche;
use MaxSem\Hiero\Blocks\Parentheses;
use MaxSem\Hiero\Parse\Input;
use PHPUnit\Framework\TestCase;

class InputTest extends TestCase
{
    public function testEmpty(): void
    {
        $input = new Input([]);

        self::assertTrue($input->eof());
        self::assertNull($input->current());
        self::assertNull($input->next());

        // Ensure next() hasn't made something funny
        self::assertTrue($input->eof());
    }

    public function testIteration(): void
    {
        $input = new Input(['a', 'b', 'c']);

        self::assertSame('a', $input->current());
        self::assertFalse($input->eof());

        self::assertSame('b', $input->next());
        self::assertSame('b', $input->current());
        self::assertFalse($input->eof());

        self::assertSame('c', $input->next());
        self::assertSame('c', $input->current());
        self::assertFalse($input->eof());

        self::assertNull($input->next());
        self::assertNull($input->current());
        self::assertTrue($input->eof());
    }

    public function testSub(): void
    {
        $input = new Input(['a', 'b', 'c', 'd', 'e'], 1);
        self::assertSame('bcde', $this->inputToString($input));

        $input = new Input(['a', 'b', 'c', 'd', 'e'], 1, 3);
        self::assertSame('bcd', $this->inputToString($input));
    }

    /**
     * @dataProvider provideFindMatchingCloser
     */
    public function testFindMatchingCloser(array $tokens, string $closerClass, ?string $expected, int $skip = 0): void
    {
        $input = new Input($tokens);

        for ($i = 0; $i < $skip; $i++) {
            $input->next();
        }

        $result = $input->findMatchingCloser($closerClass);
        if ($expected === null) {
            self::assertNull($result);
        } else {
            self::assertSame($expected, $this->inputToString($result));
        }
    }

    public static function provideFindMatchingCloser(): array
    {
        return [
            [ [], Cartouche::class, null ],
            [ ['A1', 'B1'], Cartouche::class, null ],
            [ ['<', 'test'], Cartouche::class, null, 1 ],
            [ ['foo', '<', 'test'], Cartouche::class, null ],
            [ ['<', 'test', '>'], Cartouche::class, 'test' ],
            [ ['<', 'te', 'st', '>', 'foo'], Cartouche::class, 'test' ],
            [ ['<', 'a', '<', 'b', 'c', '>', '>'], Cartouche::class, 'a<bc>' ],
            [ ['<', 'a', '<', 'b', 'c', '>', '1>', 'foo'], Cartouche::class, 'a<bc>' ],
            [ ['foo', '<', 'a', '<', 'b', 'c', '>', '1>'], Cartouche::class, 'a<bc>', 1 ],
            [ ['foo','bar', '<', 'a', '<', 'b', 'c', '>', '1>'], Cartouche::class, 'a<bc>', 2 ],
            [ ['<', 'a', '<', 'b', '!', 'c', '>', '1>'], Cartouche::class, null ],

            [ ['(', 'a', '<', 'b', 'c', '>', ')'], Parentheses::class, 'a<bc>' ],
        ];
    }

    private function inputToString(Input $input): string
    {
        $s = '';
        while (!$input->eof()) {
            $cur = $input->current();

            if ($cur === null) {
                self::fail("Unexpected null after input of '$s'");
            }

            $s .= $cur;

            $next = $input->next();
            $cur = $input->current();
            if ($next !== $cur) {
                self::fail("next() = '$next' and current() = '$cur' don't match after input of '$s'");
            }
        }

        return $s;
    }
}
