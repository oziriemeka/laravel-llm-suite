<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Support;

/**
 * Represents an image generation request.
 */
class ImageRequest
{
    public function __construct(
        public string $prompt,
        public string $size = '1024x1024',
        public ?string $model = null,
        public int $n = 1,
        public ?string $quality = null,
        public ?string $style = null,
    ) {}

    /**
     * Create an ImageRequest from an array of parameters.
     */
    public static function fromArray(array $params): static
    {
        return new static(
            prompt: $params['prompt'] ?? '',
            size: $params['size'] ?? '1024x1024',
            model: $params['model'] ?? null,
            n: $params['n'] ?? 1,
            quality: $params['quality'] ?? null,
            style: $params['style'] ?? null,
        );
    }

    /**
     * Convert to array for API requests.
     */
    public function toArray(): array
    {
        $data = [
            'prompt' => $this->prompt,
            'size' => $this->size,
            'n' => $this->n,
        ];

        if ($this->model !== null) {
            $data['model'] = $this->model;
        }

        if ($this->quality !== null) {
            $data['quality'] = $this->quality;
        }

        if ($this->style !== null) {
            $data['style'] = $this->style;
        }

        return $data;
    }
}

