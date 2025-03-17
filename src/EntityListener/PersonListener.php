<?php

namespace App\EntityListener;

use App\Entity\Person;
use Symfony\Component\String\Slugger\SluggerInterface;

class PersonListener
{
    private $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function prePersist(Person $person): void
    {
        $person->setSlug($this->generateSlug($person));
    }

    public function preUpdate(Person $person): void
    {
        $person->setSlug($this->generateSlug($person));
    }

    public function generateSlug(Person $person): string
    {
        $underscopedFullname = str_replace(' ' , '_', strtolower($person->getFullName())); ;
        $slug = $this->slugger->slug($underscopedFullname);
        return $slug;
    }
}