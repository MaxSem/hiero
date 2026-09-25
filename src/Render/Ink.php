<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Render;

use MaxSem\Hiero\ViewBox;

final readonly class Ink
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

    public static function fromViewBox(ViewBox $vb): self
    {
        return new self($vb->minX, $vb->minY, $vb->width, $vb->height);
    }

    public function combineWith(Ink $other, float $offsetX, float $offsetY): self
    {
        $x1 = min($this->minX, $other->minX + $offsetX);
        $y1 = min($this->minY, $other->minY + $offsetY);
        $x2 = max($this->minX + $this->width, $other->minX + $other->width + $offsetX);
        $y2 = max($this->minY + $this->height, $other->minY + $other->height + $offsetY);

        return self::fromTwoPoints($x1, $y1, $x2, $y2);
    }

    /**
     * @return float[]
     */
    public function toArray(): array
    {
        return [$this->minX, $this->minY, $this->width, $this->height];
    }

    /**
     * @param Ink[] $inks
     */
    public static function totalHeight(array $inks): float
    {
        return array_sum(array_map(fn ($i) => $i->height, $inks));
    }
}
