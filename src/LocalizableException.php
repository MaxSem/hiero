<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

abstract class LocalizableException extends HieroException
{
    protected function __construct(
        public readonly Error $error,
        string $message,
    ) {
        parent::__construct($message);
    }
}
