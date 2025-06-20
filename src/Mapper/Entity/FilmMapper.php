<?php

namespace App\Mapper\Entity;

use App\Entity\Film;
use App\Enum\Genres;
use App\Model\Response\Entity\Film\FilmDetail;
use App\Model\Response\Entity\Film\FilmForm;
use App\Model\Response\Entity\Film\FilmList;
use App\Model\Response\Entity\Film\FilmListItem;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Assessment;
use App\Entity\User;
use App\Entity\Person;

class FilmMapper
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    public function mapToEntityList(array $films): FilmList
    {
        $items = array_map(
            fn (Film $film) => $this->mapToEntityListItem($film, new FilmListItem($film->getId())),
            $films
        );

        return new FilmList(array_values($items));
    }

    public function mapToEntityListItem(Film $film, FilmListItem $model): FilmListItem
    {
        return $model
            ->setId($film->getId())
            ->setName($film->getName())
            ->setReleaseYear($film->getReleaseYear())
            ->setPoster($film->getPoster())
            ->setSlug($film->getSlug())
            ->setInternationalName($film->getInternationalName())
        ;
    }
    public function mapToDetail(Film $film, FilmDetail $model, string $locale = 'ru'): FilmDetail
    {
        return $model
            ->setId($film->getId())
            ->setName($film->getName())
            ->setSlogan($film->getSlogan())
            ->setGenreIds($film->getGenres())
            ->setGenreNames($this->mapGenresToNames($film->getGenres()))
            ->setReleaseYear($film->getReleaseYear())
            ->setDescription($film->getDescription())
            ->setRating($film->getRating() ?? 0.0)
            ->setAge($film->getAge())
            ->setDuration($this->setFormattedDuration($film->getDuration()))
            ->setCover($film->getCover() ?: '')
            ->setAssessments($this->mapAssessments($film->getAssessments()->toArray()))
            ->setRating(number_format($film->getRating(), 1) ?? 0.0)
            ->setPublisherData($film->getPublisher() ? $this->mapPublisherData($film->getPublisher()) : [])
            ->setActorsData($film->getActors() ? $this->mapPersonsData($film->getActors()->toArray()) : [])
            ->setTeamData($this->mapFilmTeam($film))
            ->setCreatedAt($film->getCreatedAt()->format('Y-m-d'))
            ->setUpdatedAt($film->getUpdatedAt()->format('Y-m-d'))
            ->setPoster($film->getPoster())
            ->setTrailer($film->getTrailer())
            ->setSlug($film->getSlug())
            ->setInternationalName($film->getInternationalName())
            ->setAssessmentsGraph($this->createAssessmentsGraph($film->getAssessments()->toArray()))
        ;
    }

    public function mapToForm(Film $film, FilmForm $model): FilmForm
    {
        return $model
            ->setId($film->getId())
            ->setSlogan($film->getSlogan())
            ->setName($film->getName())
            ->setGenreIds($film->getGenres())
            ->setReleaseYear($film->getReleaseYear())
            ->setActorIds($this->getActorsIds($film))
            ->setDirectorId($film->getDirectedBy() ? $film->getDirectedBy()->getId() : null)
            ->setWriterId($film->getWriter() ? $film->getWriter()->getId() : null)
            ->setProducerId($film->getProducer() ? $film->getProducer()->getId() : null)
            ->setComposerId($film->getComposer() ? $film->getComposer()->getId() : null)
            ->setCover($film->getCover() ?: '')
            ->setDuration($this->setFormattedDuration($film->getDuration()))
            ->setDescription($film->getDescription())
            ->setAge($film->getAge())
            ->setTrailer($film->getTrailer())
            ->setPoster($film->getPoster())
            ->setSlug($film->getSlug())
            ->setInternationalName($film->getInternationalName())
        ;
    }

    public function mapToListItem(Film $film): FilmListItem
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
            $film->getInternationalName()
        );
    }

    private function setFormattedDuration($duration): string
    {
        return sprintf('%02d:%02d', $duration->format('H'), $duration->format('i'));
    }

    private function getActorsIds(Film $film): array
    {
        return array_map(fn (Person $actor) => $actor->getId(), $film->getActors()->toArray());
    }

    private function mapAssessments(array $assessments): array
    {
        $assessmentsArr = array_map(
            function (Assessment $assessment) {
                return [
                  'id' => $assessment->getId(),
                  'authorId' => $assessment->getAuthor()->getId(),
                  'authorName' => $assessment->getAuthor()->getDisplayName(),
                  'authorAvatar' => $assessment->getAuthor()->getAvatar(),
                  'comment' => $assessment->getComment(),
                  'rating' => $assessment->getRating(),
                  'createdAt' => $assessment->getCreatedAt(),
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
        $mappedGraph = array_map(function ($count, $rating) use ($ratings) {
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
        ];
    }

    private function mapPersonsData(array $actors): array
    {
        return array_map(
            function (Person $actor) {
                return [
                  'slug' => $actor->getSlug(),
                  'name' => $actor->getFullname(),
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
              'slug' => $person->getSlug() ?? null,
              'name' => $person->getFullName() ?? null,
              'avatar' => $person->getAvatar() ?? null,
            ] : [];
        };

        return [
          $mapPerson($film->getDirectedBy()),
          $mapPerson($film->getWriter()),
          $mapPerson($film->getProducer()),
          $mapPerson($film->getComposer())
        ];
    }

    private function mapGenreIdsToEnums(array $genreIds): array
    {
        return array_map(
            fn (int $genreId) => Genres::from($genreId),
            $genreIds
        );
    }

    private function mapGenresToNames(array $genres): array
    {
        $genreEnums = $this->mapGenreIdsToEnums($genres);
        return array_map(
            fn (Genres $genre) => $genre->trans($this->translator),
            $genreEnums
        );
    }

}
