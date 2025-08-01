<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImageProcessorService
{
    private const HEALTH_ENDPOINT = '%s/health';
    private const COMPRESS_ENDPOINT = '%s/compress';

    public function __construct(
        #[Autowire('%image_service_url%')] private string $imageServiceUrl,
        private HttpClientInterface $httpClient
    ) {
    }

    public function compressUploadedFile(
        UploadedFile $file,
        string $filename,
        string $path,
        int $quality = 80): bool
    {
        $healthy = $this->checkHealthy();

        if (!$healthy) {
            return false;
        }
        $url = sprintf(self::COMPRESS_ENDPOINT, $this->imageServiceUrl);
        try {
            $formFields = [
                'image' => new DataPart(fopen($file->getRealPath(), 'r'),
                    $file->getClientOriginalName()),
                'quality' => (string) $quality,
            ];

            $formData = new FormDataPart($formFields);

            $response = $this->httpClient->request('POST', $url, [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ]);

            $decodedImage = base64_decode($response->toArray()['image']);
            $extension = $response->toArray()['extension'];
            $fullpath = $path.DIRECTORY_SEPARATOR.$filename.'.'.$extension;

            file_put_contents($fullpath, $decodedImage);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function checkHealthy(): bool
    {
        $url = sprintf(self::HEALTH_ENDPOINT, $this->imageServiceUrl);
        try {
            $this->httpClient->request('GET', $url);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
