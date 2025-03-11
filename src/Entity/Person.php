<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\Gender;
use App\Enum\Specialty;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Person
{
  use TimestampableEntity;
  public const DEFAULT_GENDER = Gender::MALE;

  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(length: 255)]
  private ?string $lastname = null;

  #[ORM\Column(length: 255)]
  private ?string $firstname = null;

  #[ORM\Column(type: Types::SMALLINT, enumType: Gender::class)]
  private ?Gender $gender = self::DEFAULT_GENDER;

  #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
  private ?\DateTimeInterface $birthday = null;
  
  #[ORM\Column(type: Types::SMALLINT, nullable: true)]
  private ?int $age = null;

  /**
   * @var Collection<int, Film>
   */
  #[ORM\ManyToMany(targetEntity: Film::class, mappedBy: 'actors', cascade: ['persist'])]
  private Collection $actedInFilms;


  #[ORM\Column(type: Types::JSON, enumType: Specialty::class)]
  private array $specialties;

  /**
   * @var Collection<int, Film>
   */
  #[ORM\OneToMany(targetEntity: Film::class, mappedBy: 'directedBy')]
  private Collection $directedFilms;

  /**
   * @var Collection<int, Film>
   */
  #[ORM\OneToMany(targetEntity: Film::class, mappedBy: 'producer')]
  private Collection $producedFilms;

  /**
   * @var Collection<int, Film>
   */
  #[ORM\OneToMany(targetEntity: Film::class, mappedBy: 'writer')]
  private Collection $writtenFilms;

  #[ORM\OneToMany(targetEntity: Film::class, mappedBy: 'composer')]
  private Collection $composedFilms;

  #[ORM\Column(type: Types::TEXT, nullable: true)]
  private ?string $bio = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $cover = null;

  #[ORM\OneToOne(mappedBy: 'person', cascade: ['persist', 'remove'])]
  private ?ActorRole $actorRole = null;

  #[ORM\Column(length: 255, nullable: true)]
  private ?string $avatar = null;

  #[ORM\ManyToOne(inversedBy: 'people')]
  private ?User $publisher = null;



  public function __construct()
  {
    $this->actedInFilms = new ArrayCollection();
    $this->directedFilms = new ArrayCollection();
    $this->producedFilms = new ArrayCollection();
    $this->writtenFilms = new ArrayCollection();
  }


  public function getId(): ?int
  {
    return $this->id;
  }

  public function getLastname(): ?string
  {
    return $this->lastname;
  }

  public function setLastname(string $lastname): static
  {
    $this->lastname = $lastname;

    return $this;
  }

  public function getFirstname(): ?string
  {
    return $this->firstname;
  }

  public function setFirstname(string $firstname): static
  {
    $this->firstname = $firstname;

    return $this;
  }

  public function getFullName(): string
  {
    return $this->firstname . ' ' . $this->lastname;
  }
  public function getGender(): ?Gender
  {
    return $this->gender;
  }

  public function setGender(Gender|int $gender): static
  {
    $this->gender = is_int($gender) ? Gender::from($gender) : $gender;

    return $this;
  }

  public function getBirthday(): ?\DateTimeInterface
  {
    return $this->birthday;
  }

  public function setBirthday(\DateTimeInterface $birthday): static
  {
    $this->birthday = $birthday;

    return $this;
  }

  /**
   * @return Collection<int, Film>
   */
  public function getFilms(): Collection
  {
    return $this->actedInFilms;
  }

  public function addFilm(Film $film): static
  {
    if (!$this->actedInFilms->contains($film)) {
      $this->actedInFilms->add($film);

    }

    return $this;
  }
  public function removeFilm(Film $film): static
  {
    if ($this->actedInFilms->contains($film)) {
      $this->actedInFilms->removeElement($film);
    }

    return $this;
  }

  public function updateFilms(array $actedInFilms): static
  {
    $this->actedInFilms = new ArrayCollection($actedInFilms);

    return $this;
  }

  public function __toString()
  {
    return $this->getFullName();
  }

  /**
   * @return Collection<int, Specialty>
   */


  /**
   * @return Collection<int, Film>
   */
  public function getDirectedFilms(): Collection
  {
      return $this->directedFilms;
  }

  public function addDirectedFilm(Film $directedFilm): static
  {
      if (!$this->directedFilms->contains($directedFilm)) {
          $this->directedFilms->add($directedFilm);
          $directedFilm->setDirectedBy($this);
      }

      return $this;
  }

  public function removeDirectedFilm(Film $directedFilm): static
  {
      if ($this->directedFilms->removeElement($directedFilm)) {
          // set the owning side to null (unless already changed)
          if ($directedFilm->getDirectedBy() === $this) {
              $directedFilm->setDirectedBy(null);
          }
      }

      return $this;
  }

  /**
   * @return Collection<int, Film>
   */
  public function getProducedFilms(): Collection
  {
      return $this->producedFilms;
  }

  public function addProducedFilm(Film $producedFilm): static
  {
      if (!$this->producedFilms->contains($producedFilm)) {
          $this->producedFilms->add($producedFilm);
          $producedFilm->setProducer($this);
      }

      return $this;
  }

  public function removeProducedFilm(Film $producedFilm): static
  {
      if ($this->producedFilms->removeElement($producedFilm)) {
          // set the owning side to null (unless already changed)
          if ($producedFilm->getProducer() === $this) {
              $producedFilm->setProducer(null);
          }
      }

      return $this;
  }

  /**
   * @return Collection<int, Film>
   */
  public function getWrittenFilms(): Collection
  {
      return $this->writtenFilms;
  }

  public function addWrittenFilm(Film $writtenFilm): static
  {
      if (!$this->writtenFilms->contains($writtenFilm)) {
          $this->writtenFilms->add($writtenFilm);
          $writtenFilm->setWriter($this);
      }

      return $this;
  }

  public function removeWrittenFilm(Film $writtenFilm): static
  {
      if ($this->writtenFilms->removeElement($writtenFilm)) {
          // set the owning side to null (unless already changed)
          if ($writtenFilm->getWriter() === $this) {
              $writtenFilm->setWriter(null);
          }
      }

      return $this;
  }

    /**
   * @return Collection<int, Film>
   */
  public function getComposedFilms(): Collection
  {
      return $this->composedFilms;
  }

  public function addComposedFilm(Film $composedFilm): static
  {
      if (!$this->composedFilms->contains($composedFilm)) {
          $this->composedFilms->add($composedFilm);
          $composedFilm->setComposer($this);
      }

      return $this;
  }

  public function removeComposedFilm(Film $composedFilm): static
  {
      if ($this->composedFilms->removeElement($composedFilm)) {
          // set the owning side to null (unless already changed)
          if ($composedFilm->getComposer() === $this) {
              $composedFilm->setComposer(null);
          }
      }

      return $this;
  }

  public function getAge(): ?int
  {
    $currentYear = date('Y');
    return $currentYear - $this->birthday->format('Y');
  }

  public function setAge(int $age): static
  {
    $this->birthday = new \DateTimeImmutable(date('Y') - $age);
    return $this;
  }

  public function getSpecialties(): array
  {
    return $this->specialties;
  }
  
  public function setSpecialties(array $specialties): static
  {
    $this->specialties = $specialties;

    return $this;
  }

  public function addSpecialty(Specialty $specialty): static
  {
    if (!in_array($specialty->value, $this->specialties, true)) {
      $this->specialties[] = $specialty->value;
    }

    return $this;
  }

  public function removeSpecialty(Specialty $specialty): static
  {
    $index = array_search($specialty->value, $this->specialties, true);
    if ($index !== false) {
      unset($this->specialties[$index]);
    }

    return $this;
  }

  public function getBio(): ?string
  {
      return $this->bio;
  }

  public function setBio(?string $bio): static
  {
      $this->bio = $bio;

      return $this;
  }

  public function getCover(): ?string
  {
      return $this->cover;
  }

  public function setCover(?string $cover): static
  {
      $this->cover = $cover;

      return $this;
  }

  public function getActorRole(): ?ActorRole
  {
      return $this->actorRole;
  }

  public function setActorRole(?ActorRole $actorRole): static
  {
      // unset the owning side of the relation if necessary
      if ($actorRole === null && $this->actorRole !== null) {
          $this->actorRole->setPerson(null);
      }

      // set the owning side of the relation if necessary
      if ($actorRole !== null && $actorRole->getPerson() !== $this) {
          $actorRole->setPerson($this);
      }

      $this->actorRole = $actorRole;

      return $this;
  }

  public function getAvatar(): ?string
  {
      return $this->avatar;
  }

  public function setAvatar(?string $avatar): static
  {
      $this->avatar = $avatar;

      return $this;
  }

  public function getPublisher(): ?User
  {
      return $this->publisher;
  }

  public function setPublisher(?User $publisher): static
  {
      $this->publisher = $publisher;

      return $this;
  }

}
