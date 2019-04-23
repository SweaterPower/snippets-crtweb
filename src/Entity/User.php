<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use DateTime;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 */
class User implements UserInterface
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $emailRequestToken;

    /**
     * @ORM\Column(type="datetime")
     */
    private $emailRequestDatetime;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Snippet", mappedBy="user", orphanRemoval=true)
     */
    private $snippets;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserStatus")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserRole")
     * @ORM\JoinColumn(nullable=false)
     */
    private $role;

    public function __construct()
    {
        $this->setEmailRequestToken('');
        $this->setEmailRequestDatetime(new DateTime());
        $this->setRoles([]);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmailRequestToken(): ?string
    {
        return $this->emailRequestToken;
    }

    public function setEmailRequestToken(string $emailRequestToken): self
    {
        $this->emailRequestToken = $emailRequestToken;

        return $this;
    }

    public function getEmailRequestDatetime(): ?\DateTimeInterface
    {
        return $this->emailRequestDatetime;
    }

    public function setEmailRequestDatetime(\DateTimeInterface $emailRequestDatetime): self
    {
        $this->emailRequestDatetime = $emailRequestDatetime;

        return $this;
    }

    /**
     * @return Collection|Snippet[]
     */
    public function getSnippets(): Collection
    {
        return $this->snippets;
    }

    public function addSnippet(Snippet $snippet): self
    {
        if (!$this->snippets->contains($snippet)) {
            $this->snippets[] = $snippet;
            $snippet->setUser($this);
        }

        return $this;
    }

    public function removeSnippet(Snippet $snippet): self
    {
        if ($this->snippets->contains($snippet)) {
            $this->snippets->removeElement($snippet);
            // set the owning side to null (unless already changed)
            if ($snippet->getUser() === $this) {
                $snippet->setUser(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?UserStatus
    {
        return $this->status;
    }

    public function setStatus(?UserStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getRole(): ?UserRole
    {
        return $this->role;
    }

    public function setRole(?UserRole $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function generateEmailToken()
    {
        $this->setEmailRequestToken($this->getToken());
        $this->setEmailRequestDatetime(new DateTime('now'));
    }

    function getToken(): string
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }

    //в минутах
    public function getConfirmTokenLifetime(): int
    {
        $now = new DateTime('now');
        $time = (int) round(($now->getTimeStamp() - $this->getEmailRequestDatetime()->getTimestamp()) / 60);
        
        return $time;
    }

    public function eraseConfirmToken()
    {
        $this->setEmailRequestToken('erased_token');
    }
}
