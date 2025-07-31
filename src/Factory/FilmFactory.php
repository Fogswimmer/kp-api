<?php

namespace App\Factory;

use App\Entity\Film;
use App\Enum\Genres;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Film>
 */
final class FilmFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Film::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->sentence(2),
            'internationalName' => self::faker()->sentence(2),
            'age' => self::faker()->randomNumber(),
            'createdAt' => self::faker()->dateTime(),
            'description' => self::faker()->text(),
            'duration' => \DateTimeImmutable::createFromMutable(self::faker()->datetime()),
            'genres' => self::faker()->randomElements(Genres::cases(),
                rand(1, count(Genres::cases()))),
            'rating' => self::faker()->randomFloat(),
            'releaseYear' => self::faker()->randomNumber(),
            'updatedAt' => self::faker()->dateTime(),
            'directedBy' => PersonFactory::new(),
            'producer' => PersonFactory::new(),
            'writer' => PersonFactory::new(),
            'composer' => PersonFactory::new(),
            'actors' => PersonFactory::new()->many(3),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Film $film): void {})
        ;
    }
}
