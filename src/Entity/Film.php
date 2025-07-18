<?php

namespace App\Entity;

use App\Enum\Genres;
use App\Repository\FilmRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: FilmRepository::class)]
class Film
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::JSON)]
    private array $genres = [];

    #[ORM\Column]
    private ?int $releaseYear = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::FLOAT)]
    private ?float $rating = null;

    private string $preview = '';

    private array $gallery = [];

    /**
     * @var Collection<int, Person>
     */
    #[ORM\ManyToMany(targetEntity: Person::class, inversedBy: 'actedInFilms', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'film_person')]
    private Collection $actors;

    #[ORM\ManyToOne(inversedBy: 'directedFilms')]
    private ?Person $directedBy = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    private ?\DateTimeImmutable $duration = null;

    #[ORM\Column]
    private ?int $age = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slogan = null;

    #[ORM\ManyToOne(inversedBy: 'producedFilms')]
    private ?Person $producer = null;

    #[ORM\ManyToOne(inversedBy: 'writtenFilms')]
    private ?Person $writer = null;

    #[ORM\ManyToOne(inversedBy: 'composedFilms')]
    private ?Person $composer = null;

    /**
     * @var Collection<int, Assessment>
     */
    #[ORM\OneToMany(targetEntity: Assessment::class, mappedBy: 'film', cascade: ['persist', 'remove'])]
    private Collection $assessments;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cover = null;

    /**
     * @var Collection<int, ActorRole>
     */
    #[ORM\OneToMany(targetEntity: ActorRole::class, mappedBy: 'film')]
    private Collection $actorRoles;

    #[ORM\ManyToOne(inversedBy: 'publisher')]
    private ?User $publisher = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $poster = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $trailer = null;

    #[ORM\Column(length: 255, nullable: true, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $internationalName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $budget = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fees = null;

    public function __construct()
    {
        $this->actors = new ArrayCollection();
        $this->assessments = new ArrayCollection();
        $this->actorRoles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getGenres(): array
    {
        return $this->genres;
    }

    public function setGenres(array $genres): static
    {
        $this->genres = $genres;

        return $this;
    }

    public function addGenre(Genres $genre): static
    {
        if (!in_array($genre->value, $this->genres, true)) {
            $this->genres[] = $genre->value;
        }

        return $this;
    }

    public function removeGenre(Genres $genre): static
    {
        $index = array_search($genre->value, $this->genres, true);
        if ($index !== false) {
            unset($this->genres[$index]);
        }

        return $this;
    }

    public function getReleaseYear(): ?int
    {
        return $this->releaseYear;
    }

    public function setReleaseYear(int $releaseYear): static
    {
        $this->releaseYear = $releaseYear;

        return $this;
    }

    /**
     * @return Collection<int, Person>
     */
    public function getActors(): Collection
    {
        return $this->actors;
    }

    public function addActor(Person $actor): static
    {
        if (!$this->actors->contains($actor)) {
            $this->actors->add($actor);
        }

        return $this;
    }

    public function removeActor(Person $actor): static
    {
        $this->actors->removeElement($actor);

        return $this;
    }

    public function updateActors(array $actors): static
    {
        $this->actors = new ArrayCollection($actors);

        return $this;
    }

    public function getPreview(): string
    {
        return $this->preview;
    }

    public function setPreview(string $preview): static
    {
        $this->preview = $preview;

        return $this;
    }

    public function getGallery(): array
    {
        return $this->gallery;
    }

    public function setGallery(array $gallery): static
    {
        $this->gallery = $gallery;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(float $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getDirectedBy(): ?Person
    {
        return $this->directedBy;
    }

    public function setDirectedBy(?Person $directedBy): static
    {
        $this->directedBy = $directedBy;

        return $this;
    }

    public function getDuration(): ?\DateTimeImmutable
    {
        return $this->duration;
    }

    public function setDuration(\DateTimeImmutable $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getSlogan(): ?string
    {
        return $this->slogan;
    }

    public function setSlogan(?string $slogan): static
    {
        $this->slogan = $slogan;

        return $this;
    }

    public function getProducer(): ?Person
    {
        return $this->producer;
    }

    public function setProducer(?Person $producer): static
    {
        $this->producer = $producer;

        return $this;
    }

    public function getWriter(): ?Person
    {
        return $this->writer;
    }

    public function setWriter(?Person $writer): static
    {
        $this->writer = $writer;

        return $this;
    }

    public function getComposer(): ?Person
    {
        return $this->composer;
    }

    public function setComposer(?Person $composer): static
    {
        $this->composer = $composer;

        return $this;
    }

    /**
     * @return Collection<int, Assessment>
     */
    public function getAssessments(): Collection
    {
        return $this->assessments;
    }

    public function addAssessment(Assessment $assessment): static
    {
        if (!$this->assessments->contains($assessment)) {
            $this->assessments->add($assessment);
            $assessment->setFilm($this);
        }

        return $this;
    }

    public function removeAssessment(Assessment $assessment): static
    {
        if ($this->assessments->removeElement($assessment)) {
            // set the owning side to null (unless already changed)
            if ($assessment->getFilm() === $this) {
                $assessment->setFilm(null);
            }
        }

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

    /**
     * @return Collection<int, ActorRole>
     */
    public function getActorRoles(): Collection
    {
        return $this->actorRoles;
    }

    public function addActorRole(ActorRole $actorRole): static
    {
        if (!$this->actorRoles->contains($actorRole)) {
            $this->actorRoles->add($actorRole);
            $actorRole->setFilm($this);
        }

        return $this;
    }

    public function removeActorRole(ActorRole $actorRole): static
    {
        if ($this->actorRoles->removeElement($actorRole)) {
            // set the owning side to null (unless already changed)
            if ($actorRole->getFilm() === $this) {
                $actorRole->setFilm(null);
            }
        }

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

    public function getPoster(): ?string
    {
        return $this->poster;
    }

    public function setPoster(?string $poster): static
    {
        $this->poster = $poster;

        return $this;
    }

    public function getTrailer(): ?string
    {
        return $this->trailer;
    }

    public function setTrailer(?string $trailer): static
    {
        $this->trailer = $trailer;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getInternationalName(): ?string
    {
        return $this->internationalName;
    }

    public function setInternationalName(?string $internationalName): static
    {
        $this->internationalName = $internationalName;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getBudget(): ?string
    {
        return $this->budget;
    }

    public function setBudget(?string $budget): static
    {
        $this->budget = $budget;

        return $this;
    }

    public function getFees(): ?string
    {
        return $this->fees;
    }

    public function setFees(?string $fees): static
    {
        $this->fees = $fees;

        return $this;
    }
}
