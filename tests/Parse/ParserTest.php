<?php

declare(strict_types=1);

namespace Tests\Hiero\Parse;

use MaxSem\Hiero\Blocks\Document;
use MaxSem\Hiero\Blocks\Hieroglyph;
use MaxSem\Hiero\Blocks\Juxtaposition;
use MaxSem\Hiero\Blocks\Line;
use MaxSem\Hiero\Blocks\Subdivision;
use MaxSem\Hiero\Blocks\VerbatimText;
use MaxSem\Hiero\ErrorCodes;
use MaxSem\Hiero\HieroglyphModifiers;
use MaxSem\Hiero\Parse\ParseContext;
use MaxSem\Hiero\Parse\ParseOptions;
use MaxSem\Hiero\Parse\Parser;
use MaxSem\Hiero\Parse\Tokenizer;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
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
        $defaultModifiers = new HieroglyphModifiers('', 0, false);
        $mirrorModifiers = new HieroglyphModifiers('\\', 0, true);
        $r1Modifiers = new HieroglyphModifiers('\\r1', 90, false);
        $t3Modifiers = new HieroglyphModifiers('\\t3', 270, true);

        return [
            'hieroglyph' =>
            [
                'input' => 'A1',
                'hieroglyph' => 'A1',
                'modifiers' => $defaultModifiers,
            ],
            'hieroglyph, lowercase' =>
            [
                'input' => 'b1',
                'hieroglyph' => 'B1',
                'modifiers' => $defaultModifiers,
            ],
            'phonetic' =>
            [
                'input' => 'p',
                'hieroglyph' => 'Q3',
                'modifiers' => $defaultModifiers,
            ],
            'phonetic, uppercase' =>
            [
                'input' => 'P',
                'hieroglyph' => 'Q3',
                'modifiers' => $defaultModifiers,
            ],
            'hieroglyph, mirrored' =>
            [
                'input' => 'A1\\',
                'hieroglyph' => 'A1',
                'modifiers' => $mirrorModifiers,
            ],
            'phonetic, rotated' =>
            [
                'input' => 'p\\r1',
                'hieroglyph' => 'Q3',
                'modifiers' => $r1Modifiers,
            ],
            'phonetic, rotated and mirrored' =>
            [
                'input' => 'p\\t3',
                'hieroglyph' => 'Q3',
                'modifiers' => $t3Modifiers,
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
     * @dataProvider provideOperators
     */
    public function testOperators(string $input, callable $assert): void
    {
        $parser = new Parser(new Tokenizer(), new ParseOptions());
        $output = $parser->parse($input);
        $result = $output->result;

        self::assertInstanceOf(Document::class, $result);
        self::assertEmpty($output->errors);

        $line = $result->innerBlocks[0];
        self::assertInstanceOf(Line::class, $line);

        $assert($line->innerBlocks);
    }

    public static function provideOperators(): array
    {
        return [
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
        ];
    }
}
