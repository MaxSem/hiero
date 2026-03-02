<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Parse;

use MaxSem\Hiero\HieroException;

class ParseException extends HieroException
{
    public function __construct(
        public readonly Error $error,
    ) {
        parent::__construct('Parse error');
    }
}
