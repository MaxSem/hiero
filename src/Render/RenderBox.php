<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

use DOMElement;
use MaxSem\Hiero\ViewBox;

final readonly class RenderBox
{
    public function __construct(
        public DOMElement $output,
        public ViewBox $viewBox,
        /**
         * @var int|null Y coordinate of the baseline or null for N/A
         */
        public ?int $baseline = null,
    ) {
    }

    public function getBaseline(): int
    {
        return $this->baseline ?? $this->viewBox->height;
    }

    /**
     * @param RenderBox[] $boxes
     */
    public static function maxBaseline(array $boxes): int
    {
        if (!$boxes) {
            return 0;
        }

        return max(array_map(fn (RenderBox $box) => $box->getBaseline(), $boxes));
    }
}
