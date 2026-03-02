<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Parse;

final readonly class ParseOptions
{
    public function __construct(
        public bool $logErrorBacktraces = false,
        public bool $throwOnErrors = true,
    ) {
    }
}
