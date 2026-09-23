<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

final readonly class Box
{
    public function __construct(
        public float $minX,
        public float $minY,
        public float $width,
        public float $height,
    ) {
    }

    public static function fromTwoPoints(float $x1, float $y1, float $x2, float $y2): self
    {
        return new self($x1, $y1, $x2 - $x1, $y2 - $y1);
    }

    /**
     * @return float[]
     */
    public function toArray(): array
    {
        return [$this->minX, $this->minY, $this->width, $this->height];
    }
}
