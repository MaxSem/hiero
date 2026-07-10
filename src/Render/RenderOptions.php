<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

use MaxSem\Hiero\AbstractOptions;

final readonly class RenderOptions extends AbstractOptions
{
    public function __construct(
        bool $throwOnErrors = true,
        bool $logErrorBacktraces = false,
        /**
         * @var string|null Hieroglyph color: valid CSS color or null to not set and default to black.
         */
        public ?string $color = null,
        /**
         * @var string|null Background: CSS color or null for transparent.
         */
        public ?string $background = null,
        /**
         * Content of rendered SVG's <style> tag or null to not set. Will be overridden by the options above.
         *
         * @var string|null
         */
        public ?string $style = null,
    ) {
        parent::__construct(
            throwOnErrors: $throwOnErrors,
            logErrorBacktraces: $logErrorBacktraces,
        );
    }
}
