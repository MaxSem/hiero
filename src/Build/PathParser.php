<?php

declare(strict_types=1);

namespace MaxSem\Hiero\Build;

use DOMDocument;
use Exception;
use MaxSem\Hiero\Render\Ink;

// phpcs:disable PSR2.Methods.FunctionCallSignature.MultipleArguments

/**
 * Doesn't properly calculate curve bounds, doesn't seem to be a problem at the time though.
 */
class PathParser
{
    private float $x = 0;
    private float $y = 0;

    private float $minX = PHP_INT_MAX;
    private float $maxX = PHP_INT_MIN;
    private float $minY = PHP_INT_MAX;
    private float $maxY = PHP_INT_MIN;

    /**
     * @return Ink|null Ink box or null if no ink was detected
     */
    public function getInk(): ?Ink
    {
        if ($this->minX == PHP_INT_MAX) {
            return null;
        }

        return Ink::fromTwoPoints($this->minX, $this->minY, $this->maxX, $this->maxY);
    }

    public function parseSvg(DOMDocument $dom): void
    {
        foreach ($dom->getElementsByTagName('path') as $i => $path) {
            if (!$path->hasAttribute('d')) {
                throw new Exception("<path> element #$i has no 'd' attribute");
            }

            $this->parsePath($path->getAttribute('d'));
        }
    }

    public function parsePath(string $path): void
    {
        $matches = [];
        preg_match_all('/([MmLlHhVvCcSsQqTtAaZz])([^MmLlHhVvCcSsQqTtAaZz]*)/', $path, $matches);
        [ , $commands, $paramLists] = $matches;

        $paramLists = array_map(fn ($p) => preg_split('/ +/', trim($p)) ?: [], $paramLists);
        $paramLists = array_map(fn ($p1) => array_filter($p1, fn ($p2) => trim($p2) !== ''), $paramLists);

        foreach ($commands as $i => $command) {
            $params = $paramLists[$i];
            $params = array_map(floatval(...), $params);

            switch ($command) {
                case 'M':
                    foreach (array_chunk($params, 2) as $chunk) {
                        $this->moveTo($chunk[0], $chunk[1]);
                    }
                    break;
                case 'm':
                    foreach (array_chunk($params, 2) as $chunk) {
                        $this->moveTo($this->x + $chunk[0], $this->y + $chunk[1]);
                    }
                    break;

                case 'L':
                    foreach (array_chunk($params, 2) as $chunk) {
                        $this->lineTo($chunk[0], $chunk[1]);
                    }
                    break;
                case 'l':
                    foreach (array_chunk($params, 2) as $chunk) {
                        $this->lineTo($this->x + $chunk[0], $this->y + $chunk[1]);
                    }
                    break;

                case 'H':
                    foreach ($params as $x) {
                        $this->lineTo($x, $this->y);
                    }
                    break;
                case 'h':
                    foreach ($params as $x) {
                        $this->lineTo($this->x + $x, $this->y);
                    }
                    break;

                case 'V':
                    foreach ($params as $y) {
                        $this->lineTo($this->x, $y);
                    }
                    break;
                case 'v':
                    foreach ($params as $y) {
                        $this->lineTo($this->x, $this->y + $y);
                    }
                    break;

                case 'C':
                    foreach (array_chunk($params, 6) as $chunk) {
                        $this->bezierCurveTo($chunk[0], $chunk[1], $chunk[2], $chunk[3], $chunk[4], $chunk[5]);
                    }
                    break;
                case 'c':
                    foreach (array_chunk($params, 6) as $chunk) {
                        $this->bezierCurveTo(
                            $this->x + $chunk[0], $this->y + $chunk[1],
                            $this->x + $chunk[2], $this->y + $chunk[3],
                            $this->x + $chunk[4], $this->y + $chunk[5]
                        );
                    }
                    break;

                case 'S':
                    foreach (array_chunk($params, 4) as $chunk) {
                        $this->smoothBezierCurveTo($chunk[0], $chunk[1], $chunk[2], $chunk[3]);
                    }
                    break;
                case 's':
                    foreach (array_chunk($params, 4) as $chunk) {
                        $this->smoothBezierCurveTo(
                            $this->x + $chunk[0], $this->y + $chunk[1],
                            $this->x + $chunk[2], $this->y + $chunk[3]
                        );
                    }
                    break;

                case 'Q':
                    foreach (array_chunk($params, 4) as $chunk) {
                        $this->quadraticCurveTo($chunk[0], $chunk[1], $chunk[2], $chunk[3]);
                    }
                    break;
                case 'q':
                    foreach (array_chunk($params, 4) as $chunk) {
                        $this->quadraticCurveTo(
                            $this->x + $chunk[0], $this->y + $chunk[1],
                            $this->x + $chunk[2], $this->y + $chunk[3]
                        );
                    }
                    break;

                case 'T':
                    foreach (array_chunk($params, 2) as $chunk) {
                        $this->smoothQuadraticCurveTo($chunk[0], $chunk[1]);
                    }
                    break;
                case 't':
                    foreach (array_chunk($params, 2) as $chunk) {
                        $this->smoothQuadraticCurveTo($this->x + $chunk[0], $this->y + $chunk[1]);
                    }
                    break;

                case 'A':
                case 'a':
                    throw new Exception('Arc, really?');

                case 'Z':
                case 'z':
                    break;

                default:
                    throw new Exception("Unknown path command: $command");
            }
        }
    }

    private function lineTo(float $x, float $y): void
    {
        $this->moveTo($x, $y);
    }

    private function moveTo(float $x, float $y): void
    {
        $this->registerPoint($x, $y);
        $this->x = $x;
        $this->y = $y;
    }

    private function quadraticCurveTo(float $cpx, float $cpy, float $x, float $y): void
    {
        $this->moveTo($x, $y);
    }

    private function smoothQuadraticCurveTo(float $x, float $y): void
    {
        $this->moveTo($x, $y);
    }

    private function bezierCurveTo(float $cp1x, float $cp1y, float $cp2x, float $cp2y, float $x, float $y): void
    {
        $this->moveTo($x, $y);
    }

    private function smoothBezierCurveTo(float $cp2x, float $cp2y, float $x, float $y): void
    {
        $this->moveTo($x, $y);
    }

    private function registerPoint(float $x, float $y): void
    {
        $this->minX = min($x, $this->minX);
        $this->maxX = max($x, $this->maxX);
        $this->minY = min($y, $this->minY);
        $this->maxY = max($y, $this->maxY);
    }
}
