<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Specialty: int implements TranslatableInterface
{
    case ACTOR = 1;
    case DIRECTOR = 2;
    case PRODUCER = 3;
    case WRITER = 4;
    case COMPOSER = 5;
    case EDITOR = 6;

    public function trans(TranslatorInterface $translator, ?string $locale = null, $gender = Gender::MALE): string
    {
        return match ($this) {
            self::ACTOR => $translator->trans($gender === Gender::MALE ? 'actor' : 'actress', locale: $locale, domain: 'specialty'),
            self::DIRECTOR => $translator->trans('director', locale: $locale, domain: 'specialty'),
            self::PRODUCER => $translator->trans('producer', locale: $locale, domain: 'specialty'),
            self::WRITER => $translator->trans('writer', locale: $locale, domain: 'specialty'),
            self::COMPOSER => $translator->trans('composer', locale: $locale, domain: 'specialty'),
            self::EDITOR => $translator->trans('editor', locale: $locale, domain: 'specialty'),
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function isValid(int $value): bool
    {
        return in_array($value, self::getValues(), true);
    }

    public static function matchIdAndSpecialty(int $id): self
    {
        return match ($id) {
            1 => self::ACTOR,
            2 => self::DIRECTOR,
            3 => self::PRODUCER,
            4 => self::WRITER,
            5 => self::COMPOSER,
            6 => self::EDITOR,
            default => throw new \InvalidArgumentException("Unknown specialty ID: $id"),
        };
    }

    public static function matchIdAndTranslation(int $id, ?TranslatorInterface $translator = null, ?string $locale = null): string
    {
        return self::matchIdAndSpecialty($id)->trans($translator, $locale);
    }

    public static function list(?TranslatorInterface $translator = null, ?string $locale = null): array
    {
        return array_map(function (Specialty $case) use ($translator, $locale) {
            return [
                'name' => $translator ? $case->trans($translator, $locale) : $case->name,
                'value' => $case->value,
            ];
        }, self::cases());
    }

    public static function random(): self
    {
        $cases = self::cases();

        return $cases[array_rand($cases)];
    }

    public static function randomMany(int $min = 1, int $max = 3): array
    {
        $cases = self::cases();
        shuffle($cases);

        return array_slice($cases, 0, rand($min, min($max, count($cases))));
    }
}
