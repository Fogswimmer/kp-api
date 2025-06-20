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
        $uniqueId = uniqid();
        $slug = '';
        if ($person->getInternationalName() !== null) {
            $slug = $this->slugger->slug($person->getInternationalName())->lower();
            if ($person->getSlug() === $slug) {
                $slug = $this->slugger->slug($person->getInternationalName())->lower() . '-' . $uniqueId;
            }
        } else {
            $slug = $this->slugger->slug($person->getFullName())->lower() . '-' . $uniqueId;
        }

        return $slug;
    }
}
