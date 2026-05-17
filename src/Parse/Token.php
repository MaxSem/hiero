<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Parse;

use MaxSem\Hiero\Blocks\Cartouche;
use MaxSem\Hiero\Blocks\Juxtaposition;
use MaxSem\Hiero\Blocks\Parentheses;
use MaxSem\Hiero\Blocks\Subdivision;

class Token
{
    public const BLOCK_OPENERS = [
        '<' => Cartouche::class,
        '<1' => Cartouche::class,
        '<2' => Cartouche::class,
        '<h1' => Cartouche::class,
        '<h2' => Cartouche::class,
        '<h3' => Cartouche::class,

        '(' => Parentheses::class,
    ];

    public const BLOCK_CLOSERS = [
        '>' => Cartouche::class,
        '1>' => Cartouche::class,
        '2>' => Cartouche::class,
        'h1>' => Cartouche::class,
        'h2>' => Cartouche::class,
        'h3>' => Cartouche::class,

        ')' => Parentheses::class,
    ];

    public const OPERATORS = [
        ':' => Subdivision::class,
        '*' => Juxtaposition::class,
    ];

    public const OPERATOR_PRECEDENCE = [
        ':' => 1,
        '*' => 2,
    ];

    public const VOIDS = [
        self::HALF_WIDTH_VOID => true,
        self::FULL_WIDTH_VOID => true,
    ];

    public const EOL = '!';
    public const SEPARATOR = '-';
    public const HALF_WIDTH_VOID = '.';
    public const FULL_WIDTH_VOID = '..';

    public static function isVoid(string $token): bool
    {
        return isset(self::VOIDS[$token]);
    }
}
