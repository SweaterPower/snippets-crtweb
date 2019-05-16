<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Validator\Constraints as Assert;
use DateTime;

/**
 * Хранит всю основную информацию о пользователе.
 * 
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"email"}, message="There is already an account with this email")
 * @ORM\Table(indexes={@ORM\Index(name="username_idx", columns={"username"})})
 */
class User implements UserInterface
{

    /**
     * Уникальный идентификатор.
     * 
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Адрес электронной почты.
     * 
     * @Assert\Email
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * Пароль пользователя в зашифрованном виде.
     * 
     * @var string The hashed password
     * @ORM\Column(type="string", nullable=true)
     */
    private $password;

    /**
     * Имя пользователя.
     * 
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255, unique = true)
     */
    private $username;

    /**
     * Уникальный токен для подтверждения электронной почты.
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $emailRequestToken;

    /**
     * Время отправки сообщения для подвтерждения на электронную почту.
     * 
     * @ORM\Column(type="datetime")
     */
    private $emailRequestDatetime;

    /**
     * Сниппеты пользователя.
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Snippet", mappedBy="owner", orphanRemoval=true)
     */
    private $snippets;

    /**
     * Статус пользователя.
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\UserStatus")
     * @ORM\JoinColumn(nullable=false)
     */
    private $status;

    /**
     * Роли пользователя.
     * 
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="App\Entity\UserRole")
     */
    private $roles;

    public function __construct()
    {
        $this->setEmailRequestToken('');
        $this->roles = new ArrayCollection();
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
    public function getSnippets(): array
    {
        return $this->snippets;
    }

    public function addSnippet(Snippet $snippet): self
    {
        if (!$this->snippets->contains($snippet)) {
            $this->snippets[] = $snippet;
            $snippet->setOwner($this);
        }

        return $this;
    }

    public function removeSnippet(Snippet $snippet): self
    {
        if ($this->snippets->contains($snippet)) {
            $this->snippets->removeElement($snippet);
            // set the owning side to null (unless already changed)
            if ($snippet->getOwner() === $this) {
                $snippet->setOwner(null);
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

    public function addRole(UserRole $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(UserRole $role): self
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }

        return $this;
    }

    /**
     * @see UserInterface
     * @return (Role|string)[]
     */
    public function getRoles(): array
    {
        $ret = [];
        foreach ($this->roles as $role) {
            $ret[] = $role->getCode();
        }
        return $ret;
    }

    /**
     * Обновляет токен и время появления токена
     */
    public function updateEmailToken(string $token)
    {
        $this->setEmailRequestToken($token);
        $this->setEmailRequestDatetime(new DateTime('now'));
    }

    /**
     * Время, прошедшее с момента отправки сообщения о подтверждении почты (в минутах)
     */
    public function getConfirmTokenLifetime(): int
    {
        $now = new DateTime('now');
        $time = (int) round(($now->getTimeStamp() - $this->getEmailRequestDatetime()->getTimestamp()) / 60);

        return $time;
    }

}
