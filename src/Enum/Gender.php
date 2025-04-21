<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
enum Gender: int implements TranslatableInterface
{
  case MALE = 1;
  case FEMALE = 2;

  public function trans(TranslatorInterface $translator, ?string $locale = null): string
  {
    return match ($this) {
      self::MALE => $translator->trans('male', locale: $locale, domain: 'gender'),
      self::FEMALE => $translator->trans('female', locale: $locale, domain: 'gender'),
    };
  }

  public static function list(TranslatorInterface $translator, ?string $locale = null): array
  {
    return array_map(function (Gender $case) use ($translator, $locale) {
      return [
          'name' => $translator ? $case->trans($translator, $locale) : $case->name,
          'value' => $case->value,
      ];
  }, self::cases());

  }

}