<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Parse;

use MaxSem\Hiero\HieroException;

class MaxTokensException extends HieroException
{
    public function __construct(
        public readonly int $maxTokens
    ) {
        parent::__construct("Maximum number of tokens reached: $maxTokens");
    }
}
