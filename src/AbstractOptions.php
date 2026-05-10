<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

readonly abstract class AbstractOptions
{
    public function __construct(
        public bool $throwOnErrors = true,
        public bool $logErrorBacktraces = false,
    ) {
    }
}
