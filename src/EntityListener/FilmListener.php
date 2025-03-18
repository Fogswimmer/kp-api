<?php

namespace App\EntityListener;

use App\Entity\Film;
use Symfony\Component\String\Slugger\SluggerInterface;

class FilmListener
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function prePersist(Film $film): void
    {
        $film->setSlug($this->generateSlug($film));
    }

    public function preUpdate(Film $film): void
    {
        $film->setSlug($this->generateSlug($film));
    }

    public function generateSlug(Film $film): string
    {
        $uniqueId = uniqid();
        $slug = $this->slugger->slug($film->getInternationalName())->lower();

        if ($film->getSlug() === $slug) {
            $slug = $this->slugger->slug($film->getInternationalName())->lower() . '-' . $uniqueId;
        }

        return $slug;
    }
}
