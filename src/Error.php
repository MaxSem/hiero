<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

final readonly class Error
{
    public function __construct(
        public string $code,
        /** @var mixed[] */
        public array $params,
    ) {
    }
}
