<?php
namespace App\EventListener;

use App\Entity\Film;
use App\Service\Search\FilmSearchService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Film::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Film::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: Film::class)]
class FilmIndexListener
{
    public function __construct(private FilmSearchService $filmSearchService)
    {
    }

    public function postPersist(Film $film, LifecycleEventArgs $event): void
    {
        $this->filmSearchService->indexFilm($film);
    }

    public function postUpdate(Film $film, LifecycleEventArgs $event): void
    {
        $this->filmSearchService->indexFilm($film);
    }

    public function postRemove(Film $film, LifecycleEventArgs $event): void
    {
        $this->filmSearchService->deleteFilm($film);
    }
}
