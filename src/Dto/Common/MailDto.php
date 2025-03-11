<?php
namespace App\Dto\Common;

use OpenApi\Attributes as OA;
class MailDto
{
  public function __construct(
    #[OA\Property(example: 'example@mail.com')]
    public readonly ?string $email = null,
  ) {
  }
}