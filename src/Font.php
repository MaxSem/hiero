<?php

declare(strict_types=1);

namespace MaxSem\Hiero;

class Font
{
    /** @var array<string, mixed> */
    private array $metadata;

    /** @var array<string, int[]> */
    private array $characters;

    public readonly ViewBox $boundingBox;

    public readonly ViewBox $defaultSize;

    protected function __construct(
        /** Directory where font .svg files and _font.php metadata are located */
        readonly string $path,
    ) {
        $filename = "$path/_font.php";

        if (!is_readable($filename)) {
            throw new HieroException("Font path '$path' is invalid or doesn't contain a valid metadata file.");
        }

        [
            'metadata' => $this->metadata,
            'boundingBox' => $boundingBox,
            'characters' => $this->characters
        ] = require($filename);

        $this->boundingBox = new ViewBox(...$boundingBox);
        $this->defaultSize = new ViewBox(...reset($this->characters));
    }

    public static function fromPath(string $path): self
    {
        return new Font($path);
    }

    public static function fromComposerPackage(string $packageName): self
    {
        $path = \Composer\InstalledVersions::getInstallPath($packageName);

        return new Font("$path/font");
    }

    public function has(string $char): bool
    {
        return isset($this->characters[$char]);
    }

    public function getViewBox(string $char): ?ViewBox
    {
        $box = $this->characters[$char] ?? null;

        if ($box) {
            return new ViewBox(...$box);
        }
        return null;
    }

    public function getSvg(string $char): string
    {
        if (!$this->has($char)) {
            throw new HieroException("This font has no character '$char'.");
        }

        $filename = "{$this->path}/$char.svg";

        $svg = file_get_contents($filename);

        if ($svg === false) {
            throw new HieroException("Unable to read SVG file '$filename'.");
        }

        return $svg;
    }

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
