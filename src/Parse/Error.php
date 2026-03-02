<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Parse;

final readonly class Error
{
    /**
     * Unexpected separator '$1'
     * Examples: -A1, B1--C1
     */
    public const UNEXPECTED_SEPARATOR = 'unexpected-separator';

    /**
     * Opening element '$1' without a matching closing one
     * Example: <-a-b (unclosed cartouche)
     */
    public const UNMATCHED_OPENER = 'unmatched-opener';

    /**
     * Closing element '$1' without a matching opening one
     * Example: a->
     */
    public const UNMATCHED_CLOSER = 'unmatched-closer';

    /**
     * Token '$1' doesn't look like a hieroglyph
     * Example: !fail!
     */
    public const NOT_A_HIEROGLYPH = 'not-a-hieroglyph';

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