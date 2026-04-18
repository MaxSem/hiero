<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Parse;

class Input
{
    private int $pos;
    private readonly int $length;

    public function __construct(
        /** @var string[] */
        private readonly array $tokens,
        private readonly int $start = 0,
        int $length = -1,
    ) {
        $this->length = $length >= 0
            ? $length
            : count($this->tokens) - $start;

        $this->pos = $start;
    }

    public function eof(): bool
    {
        return $this->pos >= $this->start + $this->length;
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
        for ($pos = $this->pos + 1; $pos < $this->start + $this->length; $pos++) {
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

    /**
     * @return iterable<self>
     */
    public function lines(): iterable
    {
        $start = $this->pos;
        while (!$this->eof()) {
            if ($this->tokens[$this->pos] === Token::EOL) {
                if ($this->pos > $start) {
                    yield new self($this->tokens, $start, $this->pos - $start);
                }
                $start = $this->pos + 1;
            }
            $this->pos++;
        }
        if ($this->pos > $start) {
            yield new self($this->tokens, $start, $this->pos - $start);
        }
    }

    private function subInput(int $end): Input
    {
        $result = new Input($this->tokens, $this->pos, $end - $this->pos + 1);
        $this->pos = $end + 1;

        return $result;
    }
}
