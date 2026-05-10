<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

use MaxSem\Hiero\Error;
use MaxSem\Hiero\LocalizableException;

class RenderException extends LocalizableException
{
    public function __construct(Error $error)
    {
        parent::__construct(
            $error,
            'Render error: ' . $error->key . ' '
                . json_encode($error->params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );
    }
}
