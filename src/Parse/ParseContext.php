<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Parse;

use MaxSem\Hiero\ErrorHandler;
use MaxSem\Hiero\Options;

final readonly class ParseContext
{
    public ErrorHandler $errors;

    public function __construct(
        private Options $options,
    ) {
        $this->errors = new ErrorHandler(
            ParseException::class,
            $this->options->throwOnErrors,
            $this->options->logErrorBacktraces,
        );
    }
}
