<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Parse;

use MaxSem\Hiero\LocalizableException;

class ParseException extends LocalizableException
{
    public function __construct(Error $error)
    {
        parent::__construct($error, 'Parse error: ' . $error->key);
    }
}
