<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

class ErrorHandler
{
    public function __construct(
        private readonly AbstractOptions $options,
        /**
         * @var class-string<LocalizableException>
         */
        private readonly string $exceptionClass,
    ) {
    }

    /** @var Error[] */
    private array $errors = [];

    /**
     * @throws LocalizableException
     */
    public function add(string $code, mixed ...$params): void
    {
        $backtrace = match ($this->options->logErrorBacktraces && !$this->options->throwOnErrors) {
            true => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
            default => null,
        };

        $error = new Error($backtrace, $code, $params);

        if ($this->options->throwOnErrors) {
            throw new $this->exceptionClass($error);
        }

        $this->errors[] = $error;
    }

    /**
     * @return Error[]
     */
    public function get(): array
    {
        return $this->errors;
    }
}
