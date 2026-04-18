<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Parse;

use MaxSem\Hiero\Blocks\Block;
use MaxSem\Hiero\HieroException;

final class Output
{
    public function __construct(
        private readonly ParseOptions $options,
    ) {
    }

    private ?Block $result = null;

    /** @var Error[] */
    private array $errors = [];

    public function setResult(Block $result): void
    {
        if ($this->result) {
            throw new HieroException('Overwriting the result is not allowed.');
        }

        $this->result = $result;
    }

    public function getResult(): ?Block
    {
        return $this->result;
    }

    /**
     * @throws ParseException
     */
    public function addError(string $code, mixed ...$params): void
    {
        $backtrace = match ($this->options->logErrorBacktraces && !$this->options->throwOnErrors) {
            true => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
            default => null,
        };

        $error = new Error($backtrace, $code, $params);

        if ($this->options->throwOnErrors) {
            throw new ParseException($error);
        }

        $this->errors[] = $error;
    }

    /**
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
