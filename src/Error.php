<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

final readonly class Error
{
    public function __construct(
        /**
         * @var mixed[][]|null Same format as debug_backtrace()
         */
        public ?array $backtrace,
        public string $key,
        /** @var mixed[] */
        public array $params,
    ) {
    }
}
