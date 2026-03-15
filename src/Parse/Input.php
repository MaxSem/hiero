<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Parse;

class Input
{
    private int $pos;
    private readonly int $end;

    public function __construct(
        /** @var string[] */
        private readonly array $tokens,
        int $start = 0,
        int $end = -1,
    ) {
        $this->end = $end >= 0
            ? $end
            : count($this->tokens) - 1;

        $this->pos = $start;
    }

    public function eof(): bool
    {
        return $this->pos > $this->end;
    }

    public function current(): ?string
    {
        return $this->tokens[$this->pos] ?? null;
    }

    public function next(): ?string
    {
        return $this->tokens[++$this->pos] ?? null;
    }

    public function peek(): ?string
    {
        return $this->tokens[$this->pos + 1] ?? null;
    }

    public function findMatchingCloser(string $closerClass): ?Input
    {
        $stack = [];
        for ($pos = $this->pos + 1; $pos <= $this->end; $pos++) {
            $token = $this->tokens[$pos];

            if ($token === Token::EOL) {
                return null;
            }

            $opener = Token::BLOCK_OPENERS[$token] ?? null;
            if ($opener) {
                $stack[] = $opener;
                continue;
            }

            $closer = Token::BLOCK_CLOSERS[$token] ?? null;
            if ($closer) {
                if (end($stack) === $closer) {
                    array_pop($stack);
                    continue;
                }

                if (!$stack && $closer === $closerClass) {
                    $this->pos++;
                    return $this->subInput($pos - 1);
                }
            }
        }

        return null;
    }

    private function subInput(int $end): Input
    {
        $result = new Input($this->tokens, $this->pos, $end);
        $this->pos = $end + 1;

        return $result;
    }
}
