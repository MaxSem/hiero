<?php

declare(strict_types=1);

namespace Tests\Hiero\Parse;

use MaxSem\Hiero\Blocks\Block;
use MaxSem\Hiero\Blocks\Hieroglyph;
use MaxSem\Hiero\Blocks\Juxtaposition;
use MaxSem\Hiero\Blocks\Line;
use MaxSem\Hiero\Blocks\Parentheses;
use MaxSem\Hiero\Blocks\Subdivision;
use MaxSem\Hiero\Blocks\VerbatimText;
use MaxSem\Hiero\Blocks\VoidBlock;
use MaxSem\Hiero\ErrorCodes;
use MaxSem\Hiero\HieroglyphModifiers;
use MaxSem\Hiero\Parse\ParseContext;
use MaxSem\Hiero\Parse\ParseOptions;
use MaxSem\Hiero\Parse\Parser;
use MaxSem\Hiero\Parse\Tokenizer;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    private static HieroglyphModifiers $defaultModifiers;
    private static HieroglyphModifiers $mirrorModifiers;
    private static HieroglyphModifiers $r1Modifiers;
    private static HieroglyphModifiers $t3Modifiers;

    public static function setUpBeforeClass(): void
    {
        self::init();
    }

    private static function init(): void
    {
        if (isset(self::$defaultModifiers)) {
            return;
        }

        self::$defaultModifiers = new HieroglyphModifiers('', 0, false);
        self::$mirrorModifiers = new HieroglyphModifiers('\\', 0, true);
        self::$r1Modifiers = new HieroglyphModifiers('\\r1', 90, false);
        self::$t3Modifiers = new HieroglyphModifiers('\\t3', 270, true);
    }

    /**
     * @dataProvider provideParseHieroglyph
     */
    public function testParseHieroglyph(
        string $input,
        ?string $hieroglyph = null,
        ?HieroglyphModifiers $modifiers = null,
        ?string $text = null,
        ?string $expectedError = null,
    ): void {
        if (!$hieroglyph && !$text) {
            self::fail('Invalid test data: hieroglyph and text should not both be empty');
        }

        $parser = new Parser(new Tokenizer(), new ParseOptions());
        $context = new ParseContext(new ParseOptions(throwOnErrors: false));

        $block = $parser->parseHieroglyph($input, $context);

        if ($hieroglyph !== null) {
            self::assertInstanceOf(Hieroglyph::class, $block);
            self::assertSame($hieroglyph, $block->code);
            self::assertEquals($modifiers, $block->modifiers);
        } else {
            self::assertInstanceOf(VerbatimText::class, $block);
            self::assertSame($text, $block->content);
        }

        $errors = $context->errors->get();

        if ($expectedError === null) {
            self::assertEmpty($errors);
        } else {
            self::assertCount(1, $errors);
            $errorCode = $errors[0]->key;
            self::assertSame($expectedError, $errorCode);
        }
    }

    public static function provideParseHieroglyph(): array
    {
        self::init();

        return [
            'hieroglyph' =>
            [
                'input' => 'A1',
                'hieroglyph' => 'A1',
                'modifiers' => self::$defaultModifiers,
            ],
            'hieroglyph, lowercase' =>
            [
                'input' => 'b1',
                'hieroglyph' => 'B1',
                'modifiers' => self::$defaultModifiers,
            ],
            'phonetic' =>
            [
                'input' => 'p',
                'hieroglyph' => 'Q3',
                'modifiers' => self::$defaultModifiers,
            ],
            'phonetic, uppercase' =>
            [
                'input' => 'P',
                'hieroglyph' => 'Q3',
                'modifiers' => self::$defaultModifiers,
            ],
            'hieroglyph, mirrored' =>
            [
                'input' => 'A1\\',
                'hieroglyph' => 'A1',
                'modifiers' => self::$mirrorModifiers,
            ],
            'phonetic, rotated' =>
            [
                'input' => 'p\\r1',
                'hieroglyph' => 'Q3',
                'modifiers' => self::$r1Modifiers,
            ],
            'phonetic, rotated and mirrored' =>
            [
                'input' => 'p\\t3',
                'hieroglyph' => 'Q3',
                'modifiers' => self::$t3Modifiers,
            ],

            // Error handling and recovery
            'random text' =>
            [
                'input' => 'foo',
                'hieroglyph' => null,
                'modifiers' => null,
                'text' => 'foo',
                'expectedError' => ErrorCodes::NOT_A_HIEROGLYPH,
            ],
            'random text, ignore what looks like modifiers' =>
            [
                'input' => 'foo\\r1',
                'hieroglyph' => null,
                'modifiers' => null,
                'text' => 'foo\\r1',
                'expectedError' => ErrorCodes::NOT_A_HIEROGLYPH,
            ],
            'modifiers without anything else' =>
            [
                'input' => '\\r1',
                'hieroglyph' => null,
                'modifiers' => null,
                'text' => '\\r1',
                'expectedError' => ErrorCodes::NOT_A_HIEROGLYPH,
            ],
        ];
    }

    /**
     * @dataProvider provideParse
     */
    public function testParse(string $input, callable ...$lineAssertions): void
    {
        if (!$lineAssertions) {
            self::fail('Test needs at leas one line assertion');
        }

        $parser = new Parser(new Tokenizer(), new ParseOptions());
        $output = $parser->parse($input);
        $result = $output->result;

        self::assertEmpty($output->errors);

        foreach ($lineAssertions as $i => $assert) {
            $line = $result->innerBlocks[$i];
            self::assertInstanceOf(Line::class, $line);

            $assert($line->innerBlocks);
        }
    }

    public static function provideParse(): array
    {
        self::init();

        return [
            'empty input' => [
                '',
                function (array $blocks): void {
                    self::assertCount(1, $blocks);
                    self::assertInstanceOf(VoidBlock::class, $blocks[0]);
                    self::assertEquals(VoidBlock::FULL_WIDTH, $blocks[0]->width);
                },
            ],
            'whitespace input' => [
                ' ',
                function (array $blocks): void {
                    self::assertCount(1, $blocks);
                    self::assertInstanceOf(VoidBlock::class, $blocks[0]);
                    self::assertEquals(VoidBlock::FULL_WIDTH, $blocks[0]->width);
                },
            ],
            'single hieroglyph' => [
                'A1',
                function (array $blocks): void {
                    self::assertCount(1, $blocks);

                    self::assertHieroglyph($blocks[0], 'A1');
                },
            ],
            '2 hieroglyphs with modifiers' => [
                'A1\\-mAat\\r1',
                function (array $blocks): void {
                    self::assertCount(2, $blocks);

                    self::assertHieroglyph($blocks[0], 'A1', modifiers: self::$mirrorModifiers);

                    self::assertHieroglyph($blocks[1], 'C10', 'mAat', self::$r1Modifiers);
                },
            ],
            'void block w/o separators' => [
                'A1.B1',
                function (array $blocks): void {
                    self::assertCount(3, $blocks);

                    self::assertHieroglyph($blocks[0], 'A1');
                    self::assertHieroglyph($blocks[2], 'B1');

                    self::assertInstanceOf(VoidBlock::class, $blocks[1]);
                    self::assertSame(1, $blocks[1]->width);
                },
            ],
            'void block' => [
                'A1-.-B1',
                function (array $blocks): void {
                    self::assertCount(3, $blocks);

                    self::assertHieroglyph($blocks[0], 'A1');
                    self::assertHieroglyph($blocks[2], 'B1');

                    self::assertInstanceOf(VoidBlock::class, $blocks[1]);
                    self::assertSame(1, $blocks[1]->width);
                },
            ],
            'full-width void block w/o separators' => [
                'A1..B1',
                function (array $blocks): void {
                    self::assertCount(3, $blocks);

                    self::assertHieroglyph($blocks[0], 'A1');
                    self::assertHieroglyph($blocks[2], 'B1');

                    self::assertInstanceOf(VoidBlock::class, $blocks[1]);
                    self::assertSame(2, $blocks[1]->width);
                },
            ],
            'full-width void block' => [
                'A1 .. B1',
                function (array $blocks): void {
                    self::assertCount(3, $blocks);

                    self::assertHieroglyph($blocks[0], 'A1');
                    self::assertHieroglyph($blocks[2], 'B1');

                    self::assertInstanceOf(VoidBlock::class, $blocks[1]);
                    self::assertSame(2, $blocks[1]->width);
                },
            ],
            'juxtaposition' => [
                'A1*B1',
                function (array $blocks): void {
                    self::assertCount(1, $blocks);
                    self::assertInstanceOf(Juxtaposition::class, $blocks[0]);
                    self::assertCount(2, $blocks[0]->innerBlocks);
                    self::assertSame('A1', $blocks[0]->innerBlocks[0]->code);
                    self::assertSame('B1', $blocks[0]->innerBlocks[1]->code);
                },
            ],
            'subdivision' => [
                'A1:B1',
                function (array $blocks): void {
                    self::assertCount(1, $blocks);
                    self::assertInstanceOf(Subdivision::class, $blocks[0]);
                    self::assertCount(2, $blocks[0]->innerBlocks);
                    self::assertSame('A1', $blocks[0]->innerBlocks[0]->code);
                    self::assertSame('B1', $blocks[0]->innerBlocks[1]->code);
                },
            ],
            'precedence: * binds tighter than :' => [
                'A1*B1:C1*D1',
                function (array $blocks): void {
                    self::assertCount(1, $blocks);
                    $sub = $blocks[0];
                    self::assertInstanceOf(Subdivision::class, $sub);
                    self::assertCount(2, $sub->innerBlocks);
                    self::assertInstanceOf(Juxtaposition::class, $sub->innerBlocks[0]);
                    self::assertInstanceOf(Juxtaposition::class, $sub->innerBlocks[1]);
                    self::assertSame('A1', $sub->innerBlocks[0]->innerBlocks[0]->code);
                    self::assertSame('B1', $sub->innerBlocks[0]->innerBlocks[1]->code);
                    self::assertSame('C1', $sub->innerBlocks[1]->innerBlocks[0]->code);
                    self::assertSame('D1', $sub->innerBlocks[1]->innerBlocks[1]->code);
                },
            ],
            'separator splits groups' => [
                'A1*B1 C1*D1',
                function (array $blocks): void {
                    self::assertCount(2, $blocks);
                    self::assertInstanceOf(Juxtaposition::class, $blocks[0]);
                    self::assertInstanceOf(Juxtaposition::class, $blocks[1]);
                    self::assertSame('A1', $blocks[0]->innerBlocks[0]->code);
                    self::assertSame('B1', $blocks[0]->innerBlocks[1]->code);
                    self::assertSame('C1', $blocks[1]->innerBlocks[0]->code);
                    self::assertSame('D1', $blocks[1]->innerBlocks[1]->code);
                },
            ],
            'chained juxtaposition' => [
                'A1*B1*C1',
                function (array $blocks): void {
                    self::assertCount(1, $blocks);
                    self::assertInstanceOf(Juxtaposition::class, $blocks[0]);
                    self::assertCount(3, $blocks[0]->innerBlocks);
                    self::assertSame('A1', $blocks[0]->innerBlocks[0]->code);
                    self::assertSame('B1', $blocks[0]->innerBlocks[1]->code);
                    self::assertSame('C1', $blocks[0]->innerBlocks[2]->code);
                },
            ],

            'parentheses' => [
                'A1:(B1-C1-(D1))',
                function (array $blocks): void {
                    self::assertCount(1, $blocks);
                    self::assertInstanceOf(Subdivision::class, $blocks[0]);
                    self::assertCount(2, $blocks[0]->innerBlocks);
                    self::assertSame('A1', $blocks[0]->innerBlocks[0]->code);
                    self::assertInstanceOf(Parentheses::class, $blocks[0]->innerBlocks[1]);
                    self::assertCount(3, $blocks[0]->innerBlocks[1]->innerBlocks);
                    self::assertSame('B1', $blocks[0]->innerBlocks[1]->innerBlocks[0]->code);
                    self::assertSame('C1', $blocks[0]->innerBlocks[1]->innerBlocks[1]->code);
                    self::assertInstanceOf(Parentheses::class, $blocks[0]->innerBlocks[1]->innerBlocks[2]);
                    self::assertCount(1, $blocks[0]->innerBlocks[1]->innerBlocks[2]->innerBlocks);
                    self::assertSame('D1', $blocks[0]->innerBlocks[1]->innerBlocks[2]->innerBlocks[0]->code);
                }
            ],
            'parentheses, spaced' => [
                'A1 : ( B1-C1-( D1 ) )',
                function (array $blocks): void {
                    // same checks as the previous case
                    self::assertCount(1, $blocks);
                    self::assertInstanceOf(Subdivision::class, $blocks[0]);
                    self::assertCount(2, $blocks[0]->innerBlocks);
                    self::assertSame('A1', $blocks[0]->innerBlocks[0]->code);
                    self::assertInstanceOf(Parentheses::class, $blocks[0]->innerBlocks[1]);
                    self::assertCount(3, $blocks[0]->innerBlocks[1]->innerBlocks);
                    self::assertSame('B1', $blocks[0]->innerBlocks[1]->innerBlocks[0]->code);
                    self::assertSame('C1', $blocks[0]->innerBlocks[1]->innerBlocks[1]->code);
                    self::assertInstanceOf(Parentheses::class, $blocks[0]->innerBlocks[1]->innerBlocks[2]);
                    self::assertCount(1, $blocks[0]->innerBlocks[1]->innerBlocks[2]->innerBlocks);
                    self::assertSame('D1', $blocks[0]->innerBlocks[1]->innerBlocks[2]->innerBlocks[0]->code);
                }
            ],
        ];
    }

    private static function assertHieroglyph(
        Block $block,
        string $code,
        ?string $phonetic = null,
        ?HieroglyphModifiers $modifiers = null
    ): void {
        if (!$modifiers) {
            $modifiers = self::$defaultModifiers;
        }

        self::assertInstanceOf(Hieroglyph::class, $block);
        self::assertSame($code, $block->code);
        self::assertSame($phonetic, $block->phonetic);
        self::assertEquals($modifiers, $block->modifiers);
    }
}
