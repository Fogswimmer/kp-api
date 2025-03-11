<?php
namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    
    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Ignore]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(nullable: true)]
    private ?int $age = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $about = null;

    /**
     * @var Collection<int, Assessment>
     */
    #[ORM\OneToMany(targetEntity: Assessment::class, mappedBy: 'author')]
    private Collection $assessments;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $avatar = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $displayName = null;

    /**
     * @var Collection<int, Person>
     */
    #[ORM\OneToMany(targetEntity: Person::class, mappedBy: 'publisher')]
    private Collection $people;

    /**
     * @var Collection<int, Film>
     */
    #[ORM\OneToMany(targetEntity: Film::class, mappedBy: 'publisher')]
    private Collection $publisher;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastLogin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cover = null;

    public function __construct()
    {
        $this->assessments = new ArrayCollection();
        $this->people = new ArrayCollection();
        $this->publisher = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(?int $age): static
    {
        $this->age = $age;

        return $this;
    }

    public function getAbout(): ?string
    {
        return $this->about;
    }

    public function setAbout(?string $about): static
    {
        $this->about = $about;

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
        if (! $this->assessments->contains($assessment)) {
            $this->assessments->add($assessment);
            $assessment->setAuthor($this);
        }

        return $this;
    }

    public function removeAssessment(Assessment $assessment): static
    {
        if ($this->assessments->removeElement($assessment)) {
            // set the owning side to null (unless already changed)
            if ($assessment->getAuthor() === $this) {
                $assessment->setAuthor(null);
            }
        }

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

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): static
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * @return Collection<int, Person>
     */
    public function getPeople(): Collection
    {
        return $this->people;
    }

    public function addPerson(Person $person): static
    {
        if (!$this->people->contains($person)) {
            $this->people->add($person);
            $person->setPublisher($this);
        }

        return $this;
    }

    public function removePerson(Person $person): static
    {
        if ($this->people->removeElement($person)) {
            // set the owning side to null (unless already changed)
            if ($person->getPublisher() === $this) {
                $person->setPublisher(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Film>
     */
    public function getPublisher(): Collection
    {
        return $this->publisher;
    }

    public function addPublisher(Film $publisher): static
    {
        if (!$this->publisher->contains($publisher)) {
            $this->publisher->add($publisher);
            $publisher->setPublisher($this);
        }

        return $this;
    }

    public function removePublisher(Film $publisher): static
    {
        if ($this->publisher->removeElement($publisher)) {
            // set the owning side to null (unless already changed)
            if ($publisher->getPublisher() === $this) {
                $publisher->setPublisher(null);
            }
        }

        return $this;
    }

    public function getLastLogin(): string
    {
        return $this->lastLogin ? $this->lastLogin->format('Y-m-d H:i:s') : '';
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): static
    {
        $this->lastLogin = $lastLogin;

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
}
