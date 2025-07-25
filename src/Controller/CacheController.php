<?php

namespace App\Controller;

use App\Enum\Gender;
use App\Enum\Genres;
use App\Enum\Specialty;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CacheController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator,
        private CacheInterface $cache
    ) {
    }

    #[Route(
        path: 'api/translations/{locale}/genres',
        name: 'api_translations_genres',
        methods: ['GET'])
    ]
    public function listGenres(string $locale): Response
    {
        return $this->json($this->cacheTranslation(
            'genres', $locale,
            fn () => Genres::list($this->translator,
                $locale)
        ));
    }

    #[Route(
        path: 'api/translations/{locale}/genders',
        name: 'api_translations_genders',
        methods: ['GET'])
    ]
    public function listGenders(string $locale): Response
    {
        return $this->json($this->cacheTranslation('genders',
            $locale,
            fn () => Gender::list(
                $this->translator,
                $locale)
        ));
    }

    #[Route(
        path: 'api/translations/{locale}/specialties',
        name: 'api_translations_specialties',
        methods: ['GET'])
    ]
    public function listSpecialties(string $locale): Response
    {
        return $this->json($this->cacheTranslation('specialties',
            $locale,
            fn () => Specialty::list(
                $this->translator,
                $locale)
        ));
    }

    #[Route(
        path: 'api/translations/{locale}/countries',
        name: 'api_translations_countries',
        methods: ['GET'])
    ]
    public function listCountries(string $locale): Response
    {
        return $this->json($this->cacheTranslation('countries',
            $locale,
            fn () => Countries::getNames(
                $locale)
        ));
    }

    private function cacheTranslation(string $prefix, string $locale, callable $callback): array
    {
        $cacheKey = "translations_{$prefix}_{$locale}";

        return $this->cache->get($cacheKey, fn () => $callback());
    }
}
