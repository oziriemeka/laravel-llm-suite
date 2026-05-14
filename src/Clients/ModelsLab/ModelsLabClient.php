<?php

declare(strict_types=1);

namespace Oziri\LlmSuite\Clients\ModelsLab;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Oziri\LlmSuite\Contracts\ImageClient;
use Oziri\LlmSuite\Contracts\LlmClient;
use Oziri\LlmSuite\Exceptions\ProviderRequestException;
use Oziri\LlmSuite\Support\ImageResponse;

/**
 * ModelsLab API client implementation.
 * Supports text-to-image generation using Flux and Stable Diffusion models.
 *
 * @see https://docs.modelslab.com/image-generation/overview
 */
class ModelsLabClient implements ImageClient
{
    /**
     * Base URL for ModelsLab API.
     */
    protected const BASE_URL = 'https://modelslab.com/api/v6';

    /**
     * API endpoint for text-to-image generation.
     */
    protected const ENDPOINT_TEXT2IMG = '/images/text2img';

    /**
     * Default image model.
     */
    protected const DEFAULT_MODEL = 'flux';

    /**
     * Default image size.
     */
    protected const DEFAULT_SIZE = '1024x1024';

    /**
     * Error message for failed image generation requests.
     */
    protected const ERROR_IMAGE_FAILED = 'ModelsLab image generation request failed';

    public function __construct(
        protected array $config
    ) {}

    /**
     * Get a configured HTTP client for ModelsLab API requests.
     */
    protected function http(): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)
            ->acceptJson()
            ->asJson()
            ->timeout($this->config['timeout'] ?? 120);
    }

    /**
     * Generate an image using ModelsLab API.
     *
     * Supported params:
     *   - prompt (required): Text description of the image to generate
     *   - model (optional): Model ID (default: 'flux'). Options: flux, sdxl, etc.
     *   - size (optional): Image size as 'WxH', e.g. '1024x1024' (default)
     *   - negative_prompt (optional): What to exclude from the image
     *   - num_inference_steps (optional): Steps for generation (default: 30)
     *   - guidance_scale (optional): CFG scale (default: 7.5)
     *   - seed (optional): Seed for reproducible results
     *   - samples (optional): Number of images to generate (default: 1)
     */
    public function generate(array $params): ImageResponse
    {
        [$width, $height] = $this->parseSize($params['size'] ?? self::DEFAULT_SIZE);

        $payload = [
            'key'                  => $this->config['api_key'],
            'prompt'               => $params['prompt'] ?? '',
            'model_id'             => $params['model'] ?? $this->config['image_model'] ?? self::DEFAULT_MODEL,
            'width'                => (string) $width,
            'height'               => (string) $height,
            'samples'              => (string) ($params['samples'] ?? 1),
            'num_inference_steps'  => (string) ($params['num_inference_steps'] ?? 30),
            'guidance_scale'       => $params['guidance_scale'] ?? 7.5,
            'safety_checker'       => 'no',
        ];

        if (! empty($params['negative_prompt'])) {
            $payload['negative_prompt'] = $params['negative_prompt'];
        }

        if (isset($params['seed'])) {
            $payload['seed'] = $params['seed'];
        }

        $response = $this->http()->post(self::ENDPOINT_TEXT2IMG, $payload);

        if (! $response->successful()) {
            throw ProviderRequestException::fromResponse(self::ERROR_IMAGE_FAILED, $response);
        }

        $data = $response->json();

        if (($data['status'] ?? '') === 'error') {
            throw new \RuntimeException(
                'ModelsLab API error: '.($data['message'] ?? $data['messege'] ?? 'Unknown error')
            );
        }

        $imageUrl = $data['output'][0] ?? null;

        return new ImageResponse(
            url: $imageUrl,
            raw: $data,
        );
    }

    /**
     * ModelsLab does not support chat completions.
     */
    public function isAvailable(): bool
    {
        try {
            $response = $this->http()->post('/images/text2img', [
                'key' => $this->config['api_key'],
                'prompt' => 'test',
                'model_id' => self::DEFAULT_MODEL,
                'width' => '64',
                'height' => '64',
                'samples' => '1',
                'num_inference_steps' => '1',
            ]);

            return $response->successful();
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * ModelsLab does not expose a public model listing endpoint via this SDK.
     */
    public function getAvailableModels(): array
    {
        return [
            'flux',
            'flux-dev',
            'sdxl',
            'realistic-vision-v6',
            'dreamshaper-8',
            'anything-v5',
        ];
    }

    /**
     * Parse a size string like "1024x1024" into [width, height].
     */
    protected function parseSize(string $size): array
    {
        $parts = explode('x', $size, 2);

        return [
            (int) ($parts[0] ?? 1024),
            (int) ($parts[1] ?? 1024),
        ];
    }
}
