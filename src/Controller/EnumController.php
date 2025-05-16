<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Enum\Genres;
use App\Enum\Gender;
use App\Enum\Specialty;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use App\Dto\Common\LocaleDto;

class EnumController extends AbstractController
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }
    #[Route('/api/genres/translations', name: 'genres_list')]
    public function listGenres(#[MapQueryString] LocaleDto $dto): Response
    {
        return $this->json(Genres::list($this->translator, $dto->locale), Response::HTTP_OK);
    }

    #[Route('/api/genders/translations', name: 'genders_list')]
    public function listGenders(#[MapQueryString] LocaleDto $dto): Response
    {
        return $this->json(Gender::list($this->translator, $dto->locale), Response::HTTP_OK);
    }
    #[Route('/api/specialties/translations', name: 'specialties_list')]
    public function listSpecialties(#[MapQueryString] LocaleDto $dto): Response
    {
        return $this->json(Specialty::list($this->translator, $dto->locale), Response::HTTP_OK);
    }

}
