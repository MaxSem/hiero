<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Parse;

use MaxSem\Hiero\ErrorHandler;

final class ParseContext
{
    public readonly ErrorHandler $errors;

    public function __construct(
        private readonly ParseOptions $options,
    ) {
        $this->errors = new ErrorHandler(
            $this->options,
            ParseException::class,
        );
    }
}
