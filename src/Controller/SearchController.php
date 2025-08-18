<?php
// src/Controller/SearchController.php

namespace App\Controller;

use App\Dto\Entity\Film\FilmQueryDto;
use App\Dto\Entity\Search\FilmSearchDto;
use App\Service\Search\FilmSearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/search')]
class SearchController extends AbstractController
{
    // public function __construct(
    //     private FilmSearchService $filmSearchService
    // ) {
    // }

    // #[Route('/films', name: 'api_search_films', methods: ['GET'])]
    // public function searchFilms(
    //     #[MapQueryString()] FilmQueryDto $dto
    // ): JsonResponse {


    //     $films = $this->filmSearchService->searchFilms($dto);

    //     return $this->json(array_map(fn($film) => [
    //         'id' => $film->getId(),
    //         'name' => $film->getName(),
    //         'internationalName' => $film->getInternationalName(),
    //         'releaseYear' => $film->getReleaseYear(),
    //         'country' => $film->getCountry(),
    //     ], $films));
    // }
}
