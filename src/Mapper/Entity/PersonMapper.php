<?php
namespace App\Mapper\Entity;

use App\Entity\Film;
use App\Entity\Person;
use App\Entity\User;
use App\Enum\Specialty;
use App\Model\Response\Entity\Person\PersonDetail;
use App\Model\Response\Entity\Person\PersonForm;
use App\Model\Response\Entity\Person\PersonList;
use App\Model\Response\Entity\Person\PersonListItem;
use Symfony\Contracts\Translation\TranslatorInterface;

class PersonMapper
{
  public function __construct(
    private TranslatorInterface $translator,
  ) {
  }

  public function mapToEntityList(array $persons, $locale = null): PersonList
  {
    $items = array_map(
      fn(Person $person) =>
      $this->mapToEntityListItem($person, new PersonListItem, $locale),
      $persons
    );

    return new PersonList(array_values($items));
  }

  public function mapToEntityListItem(Person $person, PersonListItem $model, ?string $locale): PersonListItem
  {
    return $model
      ->setId($person->getId())
      ->setName($person->getFullname())
      ->setAvatar($person->getAvatar() ?: '')
      ->setSlug($person->getSlug())
      ->setInternationalName($person->getInternationalName())
      ->setSpecialtyNames($this->mapSpecialtyNamesIncludingGender($person, $locale))
      ->setBio($person->getBio() ?: '')
    ;
  }

  public function mapToListItem(Person $person): PersonListItem
  {
    return new PersonListItem(
      $person->getId(),
      $person->getFullname(),
      $person->getAvatar(),
      $person->getSlug(),
      $person->getInternationalName()
    );
  }

  public function mapToDetail(Person $person, PersonDetail $model, ?string $locale = null): PersonDetail
  {
    return $model
      ->setId($person->getId())
      ->setFirstname($person->getFirstname())
      ->setLastname($person->getLastname())
      ->setGender($person->getGender()->trans($this->translator, $locale))
      ->setGenderId($person->getGender()->value)
      ->setBirthday($person->getBirthday()->format('Y-m-d'))
      ->setAge($person->getAge())
      ->setSpecialtyIds($this->mapSpecialtiesToIds($person->getSpecialties()))
      ->setSpecialtyNames($this->mapSpecialtyNamesIncludingGender($person, $locale))
      ->setBio($person->getBio() ?: '')
      ->setCover($person->getCover() ?: '')
      ->setAvatar($person->getAvatar() ?: '')
      ->setCreatedAt($person->getCreatedAt()->format('Y-m-d'))
      ->setUpdatedAt($person->getUpdatedAt()->format('Y-m-d'))
      ->setPublisherData($person->getPublisher() ? $this->mapPublisherData($person->getPublisher()) : [])
      ->setFilmWorks($this->mapToFilmWorks($person))
      ->setSlug($person->getSlug())
      ->setInternationalName($person->getInternationalName())
    ;
  }

  public function mapToForm(Person $person, PersonForm $model): PersonForm
  {
    return $model
      ->setId($person->getId())
      ->setFirstname($person->getFirstname())
      ->setLastname($person->getLastname())
      ->setGenderId($person->getGender()->value)
      ->setBirthday($person->getBirthday()->format('Y-m-d'))
      ->setActedInFilmIds($this->mapFilmsToIds($person))
      ->setSpecialtyIds($this->mapSpecialtiesToIds($person->getSpecialties()))
      ->setBio($person->getBio() ?: '')
      ->setCover($person->getCover() ?: '')
      ->setAvatar($person->getAvatar() ?: '')
      ->setAge($person->getAge())
      ->setSlug($person->getSlug())
      ->setInternationalName($person->getInternationalName())
    ;
  }

  public function mapSpecialtyNamesIncludingGender(Person $person, ?string $locale): array
  {
    return array_map(
      fn(Specialty $specialty) =>
      $specialty->trans(
        $this->translator,
        $locale,
        $person->getGender(),
      ),
      $person->getSpecialties(),
    );
  }



  private function mapFilmsToIds(Person $person): array
  {
    $films = $person->getFilms()->toArray();

    return array_map(fn(Film $film) => $film->getId(), $films);
  }

  private function mapToFilmWorks(Person $person): array
  {
      $filmWorks = [
          'actedInFilms' => [],
          'directedFilms' => [],
          'producedFilms' => [],
          'composedFilms' => [],
          'writtenFilms' => [],
      ];
  
      $specialties = $person->getSpecialties();
      foreach ($specialties as $specialty) {
          switch ($specialty) {
              case Specialty::ACTOR:
                  $films = $person->getFilms()->toArray();
                  $actedInFilms = array_map(fn(Film $film) => [
                      'slug' => $film->getSlug(),
                      'name' => $film->getName(),
                      'releaseYear' => $film->getReleaseYear(),
                      'cover' => $film->getCover() ?: '',
                  ], $films);
                  $filmWorks['actedInFilms'] = count($actedInFilms) > 0 ? $actedInFilms : null;
                  break;
  
              case Specialty::DIRECTOR:
                  $films = $person->getDirectedFilms()->toArray();
                  $directedFilms = array_map(fn(Film $film) => [
                      'slug' => $film->getSlug(),
                      'name' => $film->getName(),
                      'releaseYear' => $film->getReleaseYear(),
                      'cover' => $film->getCover() ?: '',
                  ], $films);
                  $filmWorks['directedFilms'] = count($directedFilms) > 0 ? $directedFilms : null;
                  break;
  
              case Specialty::PRODUCER:
                  $films = $person->getProducedFilms()->toArray();
                  $producedFilms = array_map(fn(Film $film) => [
                      'slug' => $film->getSlug(),
                      'name' => $film->getName(),
                      'releaseYear' => $film->getReleaseYear(),
                      'cover' => $film->getCover() ?: '',
                  ], $films);
                  $filmWorks['producedFilms'] = count($producedFilms) > 0 ? $producedFilms : null;
                  break;
  
              case Specialty::COMPOSER:
                  $films = $person->getComposedFilms()->toArray(); // Обрати внимание на это!
                  $composedFilms = array_map(fn(Film $film) => [
                      'slug' => $film->getSlug(),
                      'name' => $film->getName(),
                      'releaseYear' => $film->getReleaseYear(),
                      'cover' => $film->getCover() ?: '',
                  ], $films);
                  $filmWorks['composedFilms'] = count($composedFilms) > 0 ? $composedFilms : null;
                  break;
  
              case Specialty::WRITER:
                  $films = $person->getWrittenFilms()->toArray(); // И тут!
                  $writtenFilms = array_map(fn(Film $film) => [
                      'slug' => $film->getSlug(),
                      'name' => $film->getName(),
                      'releaseYear' => $film->getReleaseYear(),
                      'cover' => $film->getCover() ?: '',
                  ], $films);
                  $filmWorks['writtenFilms'] = count($writtenFilms) > 0 ? $writtenFilms : null;
                  break;
          }
      }
  
      $filteredFilmWorks = array_filter($filmWorks, fn($filmWork) => $filmWork !== null);
  
      return array_map(fn($filmWork) => array_values($filmWork), $filteredFilmWorks);
  }
  

  private function mapPublisherData(User $publisher): array
  {
    return [
      'id' => $publisher->getId(),
      'name' => $publisher->getDisplayName(),
    ];
  }

  private function mapSpecialtiesToIds(array $specialties)
  {
    return array_map(fn(Specialty $specialty) => $specialty->value, $specialties);
  }

}
