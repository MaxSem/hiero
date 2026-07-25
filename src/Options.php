<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

final readonly class Options
{
    public function __construct(
        /**
         * @var bool Whether errors in user input should result in an exception being thrown immediately
         *           or Hiero should attempt to work around them and return a list of errors afterwards.
         */
        public bool $throwOnErrors = true,
        /**
         * @var bool If $throwOnErrors === false, should error information include backtraces? Useful for debugging,
         *           but not much so in production.
         */
        public bool $logErrorBacktraces = false,
        /**
         * @var int|null Maximum number of tokens to parse/render or null for no limit. MaxTokensException will be
         *               thrown if exceeded. Protects against resource exhaustion.
         */
        public ?int $maxTokens = null,
        /**
         * @var string|null Foreground color: valid CSS color or null to not set and default to black.
         */
        public ?string $color = null,
        /**
         * @var string|null Background: valid CSS color or null for transparent.
         */
        public ?string $background = null,
        /**
         * Content of rendered SVG's <style> tag or null to not set. Will be overridden by the options above.
         *
         * @var string|null
         */
        public ?string $style = null,
    ) {
    }
}
