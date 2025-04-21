<?php
namespace App\Dto\Common;

use OpenApi\Attributes as OA;
class FileNameSearchDto
{
  public function __construct(
    #[OA\Property(example: ['picture-1', 'picture-2'])]
    public readonly ?array $fileNames = [],
  ) {
  }
}