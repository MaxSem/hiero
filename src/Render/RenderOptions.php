<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

use MaxSem\Hiero\AbstractOptions;
use MaxSem\Hiero\Font;

final readonly class RenderOptions extends AbstractOptions
{
    public function __construct(
        public RenderMode $mode = RenderMode::SVG,
        bool $throwOnErrors = true,
        bool $logErrorBacktraces = false,
    ) {
        parent::__construct(
            throwOnErrors: $throwOnErrors,
            logErrorBacktraces: $logErrorBacktraces,
        );
    }
}
