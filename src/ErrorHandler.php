<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

class ErrorHandler
{
    public function __construct(
        /**
         * @var class-string<LocalizableException>
         */
        private readonly string $exceptionClass,
        private readonly bool $throwOnErrors,
        private readonly bool $logErrorBacktraces,
    ) {
    }

    /** @var Error[] */
    private array $errors = [];

    /**
     * @throws LocalizableException
     */
    public function add(string $code, mixed ...$params): void
    {
        $backtrace = match ($this->logErrorBacktraces && !$this->throwOnErrors) {
            true => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
            default => null,
        };

        $error = new Error($backtrace, $code, $params);

        if ($this->throwOnErrors) {
            throw new $this->exceptionClass($error);
        }

        $this->errors[] = $error;
    }

    /**
     * @param Error[] $errors
     */
    public function mergeErrors(array $errors): void
    {
        $this->errors = array_merge($errors, $this->errors);
    }

    /**
     * @return Error[]
     */
    public function get(): array
    {
        return $this->errors;
    }
}
