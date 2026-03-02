<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Blocks;

final readonly class Subdivision extends Operator
{
    public function markup(): string
    {
        return $this->left->markup() . ':' . $this->right->markup();
    }
}
