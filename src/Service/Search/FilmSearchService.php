<?php

namespace App\Service\Search;
use App\Dto\Entity\Film\FilmQueryDto;
use App\Dto\Entity\Search\FilmSearchDto;
use App\Entity\Film;
use App\Repository\FilmRepository;
use Doctrine\ORM\EntityManagerInterface;
use Elastic\Elasticsearch\Client as ESClient;

class FilmSearchService
{
    public function __construct(
        private ESClient $client,
        private EntityManagerInterface $em,
        private FilmRepository $filmRepository
    ) {
    }

    public function indexFilm(Film $film): void
    {
        $this->client->index([
            'index' => 'films',
            'id' => $film->getId(),
            'body' => [
                'name' => $film->getName(),
                'internationalName' => $film->getInternationalName(),
                'slug' => $film->getSlug(),
                'releaseYear' => $film->getReleaseYear(),
                'genres' => $film->getGenres(),
                'description' => $film->getDescription(),
                'country' => $film->getCountry(),
                'rating' => $film->getRating(),
                'poster' => $film->getPoster(),
                'publisher' => $film->getPublisher(),
                'createdAt' => $film->getCreatedAt()->format('Y-m-d H:i:s')
            ]
        ]);
    }
    public function searchFilms(FilmQueryDto $dto): array
    {

        $shouldQueries = [];
        $term = $dto->search;

        if (!empty($term)) {
            $fields = ['name', 'internationalName', 'slogan', 'description'];

            foreach ($fields as $field) {
                $shouldQueries[] = [
                    'match' => [
                        $field => [
                            'query' => $term,
                            'fuzziness' => 'AUTO',
                            'prefix_length' => 1
                        ]
                    ]
                ];
            }
        }

        $searchBody = [
            'query' => [
                'bool' => [
                    'should' => $shouldQueries,
                    'minimum_should_match' => 1
                ]
            ]
        ];

        $result = $this->client->search([
            'index' => 'films',
            'body' => $searchBody
        ]);

        return array_filter(array_map(
            fn($hit) => $this->filmRepository->find($hit['_id']),
            $result['hits']['hits']
        ));
    }


    public function indexAllFilms(): void
    {
        if ($this->client->indices()->exists(['index' => 'films'])->asBool()) {
            $this->client->indices()->delete(['index' => 'films']);
        }

        $this->createIndex();

        $films = $this->filmRepository->findAll();
        $batch = [];

        foreach ($films as $film) {
            $batch[] = ['index' => ['_index' => 'films', '_id' => $film->getId()]];
            $batch[] = [
                'name' => $film->getName(),
                'internationalName' => $film->getInternationalName(),
                'slogan' => $film->getSlogan(),
                'description' => $film->getDescription(),
                'genres' => $film->getGenres(),
                'releaseYear' => $film->getreleaseYear(),
                'country' => $film->getCountry(),
            ];

            if (count($batch) >= 200) {
                $this->client->bulk(['body' => $batch]);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            $this->client->bulk(['body' => $batch]);
        }
    }

    public function deleteFilm(Film $film): void
    {
        $this->client->delete([
            'index' => 'films',
            'id' => $film->getId(),
        ]);
    }

    private function createIndex(): void
    {
        $this->client->indices()->create([
            'index' => 'films',
            'body' => [
                'mappings' => [
                    'properties' => [
                        'name' => ['type' => 'text', 'analyzer' => 'standard'],
                        'internationalName' => ['type' => 'text', 'analyzer' => 'standard'],
                        'slogan' => ['type' => 'text', 'analyzer' => 'standard'],
                        'description' => ['type' => 'text', 'analyzer' => 'standard'],
                        'genres' => ['type' => 'keyword'],
                        'releaseYear' => ['type' => 'integer'],
                        'country' => ['type' => 'keyword'],
                    ]
                ]
            ]
        ]);
    }
}
