<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

/**
 * Represents a viewBox of an SVG element
 * https://developer.mozilla.org/en-US/docs/Web/SVG/Reference/Attribute/viewBox
 */
final readonly class ViewBox
{
    public function __construct(
        public int $minX,
        public int $minY,
        public int $width,
        public int $height,
    ) {
    }

    public function rotate90deg(): self
    {
        return new self($this->minX, $this->minY, $this->height, $this->width);
    }

    public function shift(int $x, int $y): self
    {
        return new self($this->minX + $x, $this->minY + $y, $this->width, $this->height);
    }

    public function scale(float $scale): self
    {
        return new self(
            $this->minX,
            $this->minY,
            (int)round($this->width * $scale),
            (int)round($this->height * $scale)
        );
    }

    /**
     * @param ViewBox[] $viewBoxes
     */
    public static function maxHeight(array $viewBoxes): int
    {
        $maxHeight = 0;
        foreach ($viewBoxes as $viewBox) {
            $maxHeight = max($maxHeight, $viewBox->height);
        }

        return $maxHeight;
    }

    /**
     * @param ViewBox[] $viewBoxes
     */
    public static function maxWidth(array $viewBoxes): int
    {
        $maxWidth = 0;
        foreach ($viewBoxes as $viewBox) {
            $maxWidth = max($maxWidth, $viewBox->width);
        }

        return $maxWidth;
    }

    public function toString(): string
    {
        return "{$this->minX} {$this->minY} {$this->width} {$this->height}";
    }
}
