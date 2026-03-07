<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Parse;

use MaxSem\Hiero\Blocks\Block;
use MaxSem\Hiero\Blocks\EmptyBlock;
use MaxSem\Hiero\Blocks\EntireText;
use MaxSem\Hiero\Blocks\Hieroglyph;
use MaxSem\Hiero\Blocks\Line;
use MaxSem\Hiero\Blocks\VerbatimText;
use MaxSem\Hiero\HieroException;
use MaxSem\Hiero\HieroglyphModifiers;
use MaxSem\Hiero\Phonetics;
use MaxSem\Hiero\Unicode;

readonly class Parser
{
    public function __construct(
        private Tokenizer $tokenizer,
        private ParseOptions $options,
    ) {
    }

    /**
     * @throws ParseException
     */
    public function parse(string $content): Output
    {
        $tokens = $this->tokenizer->tokenize($content);

        $input = new Input($tokens);
        $output = new Output($this->options);

        $lines = [];
        while (!$input->eof()) {
            $blocks = $this->parseRecursive($input, $output);
            if (!$blocks) {
                $blocks = [new EmptyBlock()];
            }

            $lines[] = new Line($blocks);
        }

        if (!$lines) {
            $lines[] = new Line([new EmptyBlock()]);
        }

        $output->setResult(new EntireText($lines));

        return $output;
    }

    /**
     * @param Input $input
     * @param Output $output
     * @return Block[]
     *
     * @throws ParseException
     */
    private function parseRecursive(Input $input, Output $output): array
    {
        $result = [];

        while (!$input->eof()) {
            $cur = $input->current();

            $class = Token::BLOCK_OPENERS[$cur] ?? null;
            if ($class) {
                $innerInput = $input->findMatchingCloser($class);

                $closer = $input->next();
                if (!$innerInput) {
                    $output->addError(Error::UNMATCHED_OPENER, $cur);
                } else {
                    $result[] = new $class($cur, $innerInput, $closer);
                }

                continue;
            }

            $class = Token::BLOCK_CLOSERS[$cur] ?? null;
            if ($class) {
                $output->addError(Error::UNMATCHED_CLOSER, $cur);
                $input->next();
            }

            $operator = Token::OPERATORS[$cur] ?? null;
            if ($operator) {
                // todo
            }

            if ($cur === Token::EOL) {
                throw new HieroException('Unexpected end of line (!) in parseRecursive()');
            }

            // assume it's a hieroglyph
            $result[] = $this->parseHieroglyph($cur, $output);
        }

        return $result;
    }

    /**
     * @throws ParseException
     */
    public function parseHieroglyph(string $content, Output $output): Block
    {
        if (!preg_match('/^([a-z][a-z0-9]*)(.*?)$/i', $content, $matches)) {
            $output->addError(Error::NOT_A_HIEROGLYPH, $content);
            return new VerbatimText($content);
        }
        $symbol = $matches[1];
        $modifiers = $matches[2];

        $normalized = ucfirst(strtolower($symbol));
        if (isset(Unicode::gardinerToChar()[$normalized])) {
            return new Hieroglyph($normalized, $this->parseModifiers($modifiers, $output), $symbol);
        }

        $lc = strtolower($symbol);
        $phonetic = Phonetics::lowerCaseIndex()[$lc] ?? null;
        if ($phonetic === null) {
            $output->addError(Error::NOT_A_HIEROGLYPH, $symbol);
            return new VerbatimText($content);
        }

        return new Hieroglyph($phonetic, $this->parseModifiers($modifiers, $output), $symbol);
    }

    private const ROTATION_TABLE = [
        'r1' => -90,
        'r2' => -180,
        'r3' => -270,
        't1' => 90,
        't2' => 180,
        't3' => 270,
    ];

    public function parseModifiers(string $markup, Output $output): HieroglyphModifiers
    {
        $rotation = 0;
        $mirror = false;

        if (preg_match('/^\\\\([rt][1-3])$/', $markup, $matches)) {
            $match = $matches[1];
            $angle = self::ROTATION_TABLE[$match] ?? null;
            if ($angle === null) {
                throw new HieroException("parseModifiers(): rotation error with input '$markup'");
            }
            if ($angle > 0) {
                $mirror = true;
            }
            $rotation = abs($angle);
        } elseif ($markup === '\\') {
            $mirror = true;
        } elseif ($markup !== '') {
            $output->addError(Error::INVALID_MODIFIERS, $markup);
            $markup = '';
        }

        return new HieroglyphModifiers($markup, $rotation, $mirror);
    }
}
