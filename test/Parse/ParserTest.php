<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Test;

use MaxSem\Hiero\Blocks\Hieroglyph;
use MaxSem\Hiero\Blocks\VerbatimText;
use MaxSem\Hiero\HieroglyphModifiers;
use MaxSem\Hiero\Parse\Error;
use MaxSem\Hiero\Parse\Output;
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
        $output = new Output(new ParseOptions(throwOnErrors: false));

        $block = $parser->parseHieroglyph($input, $output);

        if ($hieroglyph !== null) {
            self::assertInstanceOf(Hieroglyph::class, $block);
            self::assertSame($hieroglyph, $block->code);
            self::assertEquals($modifiers, $block->modifiers);
        } else {
            self::assertInstanceOf(VerbatimText::class, $block);
            self::assertSame($text, $block->content);
        }

        if ($expectedError === null) {
            self::assertEmpty($output->getErrors());
        } else {
            self::assertCount(1, $output->getErrors());
            $errorCode = $output->getErrors()[0]->key;
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
                'hieroglyph' => 'p',
                'modifiers' => $defaultModifiers,
            ],
            'phonetic, uppercase' =>
            [
                'input' => 'P',
                'hieroglyph' => 'p',
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
                'hieroglyph' => 'p',
                'modifiers' => $r1Modifiers,
            ],
            'phonetic, rotated and mirrored' =>
            [
                'input' => 'p\\t3',
                'hieroglyph' => 'p',
                'modifiers' => $t3Modifiers,
            ],

            // Error handling and recovery
            'random text' =>
            [
                'input' => 'foo',
                'hieroglyph' => null,
                'modifiers' => null,
                'text' => 'foo',
                'expectedError' => Error::NOT_A_HIEROGLYPH,
            ],
            'random text, ignore what looks like modifiers' =>
            [
                'input' => 'foo\\r1',
                'hieroglyph' => null,
                'modifiers' => null,
                'text' => 'foo\\r1',
                'expectedError' => Error::NOT_A_HIEROGLYPH,
            ],
            'modifiers without anything else' =>
            [
                'input' => '\\r1',
                'hieroglyph' => null,
                'modifiers' => null,
                'text' => '\\r1',
                'expectedError' => Error::NOT_A_HIEROGLYPH,
            ],
        ];
    }
}
