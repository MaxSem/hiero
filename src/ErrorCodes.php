<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

final class ErrorCodes
{
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

    /**
     * Invalid hieroglyph modifier: '$1'
     * Example A1\foo
     */
    public const INVALID_MODIFIERS = 'invalid-modifiers';

    /**
     * Font is missing glyph for character '$1'
     */
    public const FONT_MISSING_GLYPH = 'font-missing-glyph';
}
