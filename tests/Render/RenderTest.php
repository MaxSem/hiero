<?php

declare(strict_types=1);

namespace Tests\Hiero\Render;

use Imagick;
use ImagickPixel;
use MaxSem\Hiero\Font;
use MaxSem\Hiero\Parse\ParseOptions;
use MaxSem\Hiero\Parse\Parser;
use MaxSem\Hiero\Parse\Tokenizer;
use MaxSem\Hiero\Render\Renderer;
use MaxSem\Hiero\Render\RenderOptions;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

/**
 * Run `export DUMP_TEST_IMAGES=1` before testing to save results of the last failed test:
 * - the rendered SVG to test.svg
 * - the rasterized SVG to test.png
 *
 * 4 test "characters" are available, colors are used to discern between them in the rasterized image:
 * - A1, 100x100 red
 * - A2, 100x100 green
 * - A3, 100x100, left 50x100 is blue
 * - A4, 50x100 yellow
 *
 * They're rendered on a white background.
 */
class RenderTest extends TestCase
{
    protected function setUp(): void
    {
        static $checked = null;

        if (!$checked) {
            if (!class_exists(Imagick::class)) {
                $this->markTestSkipped('ImageMagick not found, skipping rendering tests');
            }

            if (!$this->rsvgDetected()) {
                $this->markTestSkipped('rsvg not found, skipping rendering tests');
            }
        }

        parent::setUp();
    }

    /**
     * @dataProvider provideRendering
     */
    public function testRendering(string $markup, array $tests, array $errors = []): void
    {
        $parser = new Parser(new Tokenizer(), new ParseOptions());
        $parseOutput = $parser->parse($markup);

        $font = Font::fromPath(__DIR__ . '/data/font');
        $renderOptions = new RenderOptions(throwOnErrors: false);
        $renderer = new Renderer($renderOptions, $font);

        $renderOutput = $renderer->render($parseOutput->result);

        $image = $this->render($renderOutput->svg);

        foreach ($tests as $test) {
            [$x, $y, $color] = $test;
            $expectedPixel = new ImagickPixel($color);

            try {
                // Imagick shamelessly ignores out of bounds conditions, so check manually
                self::assertLessThan($image->getImageWidth(), $x, 'Test pixel must be under the image width');
                self::assertLessThan($image->getImageHeight(), $y, 'Test pixel must be under the image height');

                $pixel = $image->getImagePixelColor($x, $y);
                self::assertSame($expectedPixel->getColor(), $pixel->getColor(), "Pixel must be $color");
            } catch (AssertionFailedError $exception) {
                if (getenv('DUMP_TEST_IMAGES') !== false) {
                    $image->writeImage('test.png');
                    file_put_contents('test.svg', $renderOutput->svg);
                }

                throw $exception;
            }
        }
    }

    public static function provideRendering(): array
    {
        return [
            'single hieroglyph' => [
                'A1',
                [
                    [50, 50, 'red'],
                ],
            ],
            'mirror' => [
                'A3\\',
                [
                    [70, 50, 'blue'],
                    [0, 0, 'white'],
                ],
            ],
            'subdivision' => [
                'A1:A3',
                [
                    // Gets rendered at 50x100
                    [0, 0, 'red'],
                    [0, 70, 'blue'],
                    [30, 70, 'white'],
                ],
            ],
            'rotation' => [
                'A1\r3 A3\r2 A4\r1',
                [
                    [50,  50, 'red'],
                    [50,  50, 'red'],
                    [120, 50, 'white'],
                    [170, 50, 'blue'],
                    [250, 30, 'white'],
                    [250, 70, 'yellow'],
                ],
            ],
        ];
    }

    private function render(string $svgContent): Imagick
    {
        // ImageMagick really sucks at SVGs, we have to use an external converter
        $rsvg = new Process(['rsvg-convert', '--background-color', 'white']);
        $rsvg->setInput($svgContent);
        $rsvg->mustRun();
        $pngContent = $rsvg->getOutput();

        $image = new Imagick();
        $image->readImageBlob($pngContent);

        return $image;
    }

    private ?bool $rsvgDetected = null;

    private function rsvgDetected(): bool
    {
        if ($this->rsvgDetected === null) {
            $process = new Process(['rsvg-convert', '--version']);
            $process->run();
            $this->rsvgDetected = $process->isSuccessful();
        }

        return $this->rsvgDetected;
    }
}
