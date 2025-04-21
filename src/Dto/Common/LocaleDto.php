<?php
namespace App\Dto\Common;

use OpenApi\Attributes as OA;
class LocaleDto
{
  public function __construct(
    #[OA\Property(example: 'ru')]
    public readonly ?string $locale = 'ru',
  ) {
  }
}