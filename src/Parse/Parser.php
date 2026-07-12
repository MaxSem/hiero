<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Parse;

use MaxSem\Hiero\Blocks\Block;
use MaxSem\Hiero\Blocks\BoundedBlock;
use MaxSem\Hiero\Blocks\Document;
use MaxSem\Hiero\Blocks\VoidBlock;
use MaxSem\Hiero\Blocks\Hieroglyph;
use MaxSem\Hiero\Blocks\Line;
use MaxSem\Hiero\Blocks\UnrecognizedMarkup;
use MaxSem\Hiero\ErrorCodes;
use MaxSem\Hiero\HieroException;
use MaxSem\Hiero\HieroglyphModifiers;
use MaxSem\Hiero\Phonetics;
use MaxSem\Hiero\Unicode;

/**
 * Magic that turns input text into an AST of blocks.
 *
 * Readonly to ensure statelessness so that it can safely parse recursively.
 */
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
    public function parse(string $content): ParseOutput
    {
        $tokens = $this->tokenizer->tokenize($content);

        $input = new Input($tokens);
        $context = new ParseContext($this->options);

        $lines = [];
        foreach ($input->lines() as $line) {
            $blocks = $this->parseRecursive($line, $context);
            if (!$blocks) {
                $blocks = [new VoidBlock(VoidBlock::FULL_WIDTH)];
            }

            $lines[] = new Line($blocks);
        }

        if (!$lines) {
            $lines[] = new Line([new VoidBlock(VoidBlock::FULL_WIDTH)]);
        }

        return new ParseOutput(
            new Document($lines),
            $context->errors->get()
        );
    }

    /**
     * @return Block[]
     *
     * @throws ParseException
     */
    private function parseRecursive(Input $input, ParseContext $context): array
    {
        $result = [];
        $blocks = [];
        $operators = [];
        $lastWasBlock = false;

        while (!$input->eof()) {
            $cur = $input->current();
            if ($cur === null) {
                throw new HieroException('Logic error: eof() and current() disagree');
            }

            if ($cur === Token::SEPARATOR) {
                $this->flushGroup($blocks, $operators, $result);
                $blocks = [];
                $operators = [];
                $lastWasBlock = false;
                $input->next();
                continue;
            }

            $class = Token::BLOCK_OPENERS[$cur] ?? null;
            if ($class) {
                /** @var class-string<BoundedBlock> $class */
                $innerInput = $input->findMatchingCloser($class);
                if ($innerInput === null) {
                    $context->errors->add(ErrorCodes::UNMATCHED_OPENER, $cur);
                    $input->next();
                } else {
                    $closer = $input->current();
                    $input->next();
                    $blocks[] = new $class($cur, $this->parseRecursive($innerInput, $context), $closer);
                    $lastWasBlock = true;
                }
                continue;
            }

            $class = Token::BLOCK_CLOSERS[$cur] ?? null;
            if ($class) {
                $context->errors->add(ErrorCodes::UNMATCHED_CLOSER, $cur);
                $input->next();
                continue;
            }

            if (isset(Token::OPERATORS[$cur])) {
                if ($lastWasBlock) {
                    $operators[] = $cur;
                    $lastWasBlock = false;
                }
                $input->next();
                continue;
            }

            if ($cur === Token::HALF_WIDTH_VOID || $cur === Token::FULL_WIDTH_VOID) {
                $blocks[] = new VoidBlock(strlen($cur));
                $input->next();
                $lastWasBlock = true;
                continue;
            }

            if ($cur === Token::EOL) {
                throw new HieroException('Unexpected end of line (!) in parseRecursive()');
            }

            // assume it's a hieroglyph
            $blocks[] = $this->parseHieroglyph($cur, $context);
            $lastWasBlock = true;
            $input->next();
        }

        $this->flushGroup($blocks, $operators, $result);

        return $result;
    }

    /**
     * @param Block[] $blocks
     * @param string[] $operators
     * @param Block[] $result
     */
    private function flushGroup(array $blocks, array $operators, array &$result): void
    {
        if (!$blocks) {
            return;
        }
        $operators = array_slice($operators, 0, count($blocks) - 1);
        if (!$operators) {
            array_push($result, ...$blocks);
            return;
        }
        $result[] = $this->buildOperatorTree($blocks, $operators);
    }

    /**
     * Recursively builds an operator tree from a flat sequence of blocks and operators,
     * respecting operator precedence. Lower-precedence operators become the outermost nodes.
     *
     * @param non-empty-array<Block> $blocks
     * @param string[] $operators  length must equal count($blocks) - 1
     */
    private function buildOperatorTree(array $blocks, array $operators): Block
    {
        if (!$operators) {
            return $blocks[0];
        }

        $minPrec = PHP_INT_MAX;
        foreach ($operators as $op) {
            $prec = Token::OPERATOR_PRECEDENCE[$op];
            if ($prec < $minPrec) {
                $minPrec = $prec;
            }
        }

        $opClass = null;
        foreach ($operators as $op) {
            if (Token::OPERATOR_PRECEDENCE[$op] === $minPrec) {
                $opClass = Token::OPERATORS[$op];
                break;
            }
        }

        $currentBlocks = [$blocks[0]];
        $currentOps = [];
        $groups = [];

        for ($i = 0, $len = count($operators); $i < $len; $i++) {
            if (Token::OPERATOR_PRECEDENCE[$operators[$i]] === $minPrec) {
                $groups[] = [$currentBlocks, $currentOps];
                $currentBlocks = [$blocks[$i + 1]];
                $currentOps = [];
            } else {
                $currentOps[] = $operators[$i];
                $currentBlocks[] = $blocks[$i + 1];
            }
        }
        $groups[] = [$currentBlocks, $currentOps];

        $innerBlocks = array_map(
            fn (array $group) => $this->buildOperatorTree($group[0], $group[1]),
            $groups
        );

        return new $opClass($innerBlocks);
    }

    /**
     * @throws ParseException
     */
    public function parseHieroglyph(string $content, ParseContext $context): Block
    {
        if (!preg_match('/^([a-z][a-z0-9]*)(.*)$/i', $content, $matches)) {
            $context->errors->add(ErrorCodes::NOT_A_HIEROGLYPH, $content);
            return new UnrecognizedMarkup($content);
        }
        $symbol = $matches[1];
        $modifiers = $matches[2];

        $normalized = ucfirst(strtolower($symbol));
        if (Unicode::gardinerToCodePoint($normalized)) {
            return new Hieroglyph($normalized, null, $this->parseModifiers($modifiers, $context), $symbol);
        }

        $phonetic = Phonetics::normalize($symbol);
        if ($phonetic === null) {
            $context->errors->add(ErrorCodes::NOT_A_HIEROGLYPH, $symbol);
            return new UnrecognizedMarkup($content);
        }
        $gardinerCode = Phonetics::translateToGardiner($phonetic)
            ?? throw new HieroException("Unexpected: couldn't translate '{$phonetic}'");

        return new Hieroglyph($gardinerCode, $phonetic, $this->parseModifiers($modifiers, $context), $symbol);
    }

    private const ROTATION_TABLE = [
        'r1' => -90,
        'r2' => -180,
        'r3' => -270,
        't1' => 90,
        't2' => 180,
        't3' => 270,
    ];

    public function parseModifiers(string $markup, ParseContext $context): HieroglyphModifiers
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
            $context->errors->add(ErrorCodes::INVALID_MODIFIERS, $markup);
            $markup = '';
        }

        return new HieroglyphModifiers($markup, $rotation, $mirror);
    }
}
