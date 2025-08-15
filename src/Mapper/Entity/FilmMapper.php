<?php

namespace App\Mapper\Entity;

use App\Dto\Entity\Film\FilmDto;
use App\Entity\Assessment;
use App\Entity\Film;
use App\Entity\Person;
use App\Entity\User;
use App\Enum\Genres;
use App\Model\Response\Entity\Film\FilmDetail;
use App\Model\Response\Entity\Film\FilmForm;
use App\Model\Response\Entity\Film\FilmList;
use App\Model\Response\Entity\Film\FilmListItem;
use Symfony\Component\Intl\Countries;
use Symfony\Contracts\Translation\TranslatorInterface;

class FilmMapper
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function mapToEntityList(array $films, string $locale): FilmList
    {

        $items = array_map(
            fn(Film $film): FilmListItem =>
            $this->mapToEntityListItem(
                $film,
                new FilmListItem($film->getId()),
                $locale
            ),
            $films
        );

        return new FilmList(array_values($items));
    }

    public function mapToEntityListItem(Film $film, FilmListItem $model, string $locale): FilmListItem
    {
        return $model
            ->setId($film->getId())
            ->setName($film->getName())
            ->setReleaseYear($film->getReleaseYear())
            ->setPoster($film->getPoster())
            ->setSlug($film->getSlug())
            ->setInternationalName($film->getInternationalName())
            ->setGenreNames($this->mapGenresToNames($film->getGenres(), $locale))
        ;
    }

    public function mapToDetail(Film $film, FilmDetail $model, string $locale): FilmDetail
    {
        return $model
            ->setId($film->getId())
            ->setName($film->getName())
            ->setSlogan($film->getSlogan())
            ->setGenreIds($film->getGenres())
            ->setGenreNames($this->mapGenresToNames($film->getGenres(), $locale))
            ->setReleaseYear($film->getReleaseYear())
            ->setDescription($film->getDescription())
            ->setRating($film->getRating() ?? 0.0)
            ->setAge($film->getAge())
            ->setDuration($this->setFormattedDuration($film->getDuration()))
            ->setAssessments($this->mapAssessments($film->getAssessments()->toArray()))
            ->setRating(number_format($film->getRating(), 1) ?: 0.0)
            ->setPublisherData($this->mapPublisherData($film->getPublisher()))
            ->setActorsData($this->mapPersonsData($film->getActors()->toArray()))
            ->setTeamData($this->mapFilmTeam($film))
            ->setCreatedAt($film->getCreatedAt()->format('Y-m-d'))
            ->setUpdatedAt($film->getUpdatedAt()->format('Y-m-d'))
            ->setPoster($film->getPoster())
            ->setSlug($film->getSlug())
            ->setInternationalName($film->getInternationalName())
            ->setAssessmentsGraph($this->createAssessmentsGraph($film->getAssessments()->toArray()))
            ->setBudget($film->getBudget())
            ->setFees($film->getFees())
            ->setCountry($this->convertAlpa2CodeToCountryName($film->getCountry(), $locale))
            ->setCountryCode($film->getCountry());
    }

    public function mapToForm(Film $film, FilmForm $model): FilmForm
    {
        return $model
            ->setId($film->getId())
            ->setSlogan($film->getSlogan())
            ->setName($film->getName())
            ->setGenreIds($film->getGenres())
            ->setReleaseYear($film->getReleaseYear())
            ->setActorIds($this->mapActorsToIds($film))
            ->setDirectorId($film->getDirectedBy()->getId())
            ->setWriterId($film->getWriter()->getId())
            ->setProducerId($film->getProducer()->getId())
            ->setComposerId($film->getComposer()->getId())
            ->setDuration($this->setFormattedDuration($film->getDuration()))
            ->setDescription($film->getDescription())
            ->setAge($film->getAge())
            ->setPoster($film->getPoster())
            ->setSlug($film->getSlug())
            ->setInternationalName($film->getInternationalName())
            ->setBudget($film->getBudget())
            ->setFees($film->getFees())
            ->setCountryCode($film->getCountry());
    }

    public function mapToListItem(Film $film, string $locale): FilmListItem
    {
        return new FilmListItem(
            $film->getId(),
            $film->getName(),
            $film->getReleaseYear(),
            $film->getPoster(),
            $film->getDescription(),
            $film->getRating(),
            $this->mapAssessments(
                $film->getAssessments()->toArray()
            ),
            $film->getSlug(),
            $film->getInternationalName(),
            $this->mapGenresToNames($film->getGenres(), $locale)
        );
    }

    public function mapToDto(Film $film): FilmDto
    {
        return new FilmDto(
            $film->getName(),
            $film->getInternationalName(),
            $film->getSlogan(),
            $film->getGenres(),
            $film->getReleaseYear(),
            $this->mapActorsToIds($film),
            $film->getDirectedBy()?->$film->getDirectedBy()->getId(),
            $film->getWriter()?->$film->getWriter()->getId(),
            $film->getProducer()?->$film->getProducer()->getId(),
            $film->getComposer()?->$film->getComposer()->getId(),
            $film->getAge(),
            $film->getDescription(),
            $film->getDuration(),
            $film->getPoster(),
            $film->getBudget(),
            $film->getFees(),
            $film->getCountry()
        );
    }

    private function setFormattedDuration($duration): string
    {
        return sprintf('%02d:%02d', $duration->format('H'), $duration->format('i'));
    }

    private function mapActorsToIds(Film $film): array
    {
        return array_map(fn(Person $actor): ?int => $actor->getId(), $film->getActors()->toArray());
    }

    private function mapAssessments(array $assessments): array
    {
        $assessmentsArr = array_map(
            function (Assessment $assessment) {
                return [
                    'id' => $assessment->getId(),
                    'comment' => $assessment->getComment(),
                    'rating' => $assessment->getRating(),
                    'createdAt' => $assessment->getCreatedAt(),
                    'publisherData' => [
                        'id' => $assessment->getAuthor()->getId(),
                        'name' => $assessment->getAuthor()->getDisplayName(),
                        'age' => $assessment->getAuthor()->getAge(),
                        'avatar' => $assessment->getAuthor()->getAvatar(),
                        'about' => $assessment->getAuthor()->getAbout(),
                        'publicationsCount' => count($assessment->getAuthor()->getPublishedPersons()),
                        'assessmentsCount' => count($assessment->getAuthor()->getAssessments()),
                    ],
                ];
            },
            $assessments
        );

        usort($assessmentsArr, function ($a, $b) {
            return $b['createdAt'] <=> $a['createdAt'];
        });

        return $assessmentsArr;
    }

    private function createAssessmentsGraph(array $assessments)
    {
        $ratings = array_map(function (Assessment $assessment) {
            return $assessment->getRating();
        }, $assessments);

        $graph = array_count_values($ratings);
        $mappedGraph = array_map(function ($count, $rating) {
            return [
                'count' => $count,
                'rating' => $rating,
            ];
        }, $graph, array_keys($graph));

        usort($mappedGraph, function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        return $mappedGraph;
    }

    private function mapPublisherData(User $publisher): array
    {
        return [
            'id' => $publisher->getId(),
            'name' => $publisher->getDisplayName(),
            'age' => $publisher->getAge(),
            'avatar' => $publisher->getAvatar(),
            'about' => $publisher->getAbout(),
            'publicationsCount' => count($publisher->getPublishedFilms()),
            'assessmentsCount' => count($publisher->getAssessments()),
        ];
    }

    private function mapPersonsData(array $actors): array
    {
        return array_map(
            function (Person $actor) {
                return [
                    'slug' => $actor->getSlug(),
                    'name' => $actor->getFullname(),
                    'internationalName' => $actor->getInternationalName() ?? null,
                    'avatar' => $actor->getAvatar(),
                ];
            },
            $actors
        );
    }

    private function mapFilmTeam(Film $film): array
    {
        $mapPerson = function (?Person $person) {
            return $person ? [
                'slug' => $person->getSlug(),
                'name' => $person->getFullName(),
                'internationalName' => $person->getInternationalName(),
                'avatar' => $person->getAvatar() ?? null,
            ] : [];
        };

        return [
            $mapPerson($film->getDirectedBy()),
            $mapPerson($film->getWriter()),
            $mapPerson($film->getProducer()),
            $mapPerson($film->getComposer()),
        ];
    }

    private function mapGenreIdsToEnums(array $genreIds): array
    {
        return array_map(
            fn(int $genreId) => Genres::from($genreId),
            $genreIds
        );
    }

    private function mapGenresToNames(array $genres, string $locale): array
    {
        $genreEnums = $this->mapGenreIdsToEnums($genres);

        return array_map(
            fn(Genres $genre): string => $genre->trans($this->translator),
            $genreEnums
        );
    }

    private function convertAlpa2CodeToCountryName(?string $countryCode, $locale): string
    {
        if (!$countryCode) {
            return '';
        }
        $countryName = Countries::getName($countryCode, $locale);

        return $countryName;
    }
}
