<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Parse;

use MaxSem\Hiero\Blocks\Document;
use MaxSem\Hiero\Error;

final readonly class ParseOutput
{
    public function __construct(
        public Document $result,
        /**
         * @var Error[]
         */
        public array $errors,
    ) {
    }
}
