<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Parse;

use MaxSem\Hiero\Error;
use MaxSem\Hiero\ErrorCodes;
use MaxSem\Hiero\LocalizableException;

class MaxTokensException extends LocalizableException
{
    public function __construct(
        public readonly int $maxTokens
    ) {
        parent::__construct(
            new Error(ErrorCodes::MAX_TOKENS_EXCEEDED, [$maxTokens]),
            "Maximum number of tokens exceeded: $maxTokens",
        );
    }
}
