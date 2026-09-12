<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

final readonly class Error
{
    public function __construct(
        public string $key,
        /** @var mixed[] */
        public array $params,
    ) {
    }
}
