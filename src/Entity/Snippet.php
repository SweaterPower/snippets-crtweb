<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

/**
 * Класс для хранения сниппета.
 * 
 * @ORM\Entity(repositoryClass="App\Repository\SnippetRepository")
 * @ORM\Table(indexes={@ORM\Index(name="url_idx", columns={"url_code"})})
 */
class Snippet
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
     * Заданный владельцем заголовок.
     * 
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * Содержимое сниппета.
     * 
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     */
    private $text;

    /**
     * Код для создания уникального URL адреса
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $urlCode;

    /**
     * Владелец сниппета
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="snippets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * Уровень доступа к сниппету (публичный или приватный)
     * 
     * @ORM\Column(type="boolean")
     */
    private $isPrivate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getUrlCode(): ?string
    {
        return $this->urlCode;
    }

    public function setUrlCode(string $urlCode): self
    {
        $this->urlCode = $urlCode;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $user): self
    {
        $this->owner = $user;

        return $this;
    }

    public function getIsPrivate(): ?bool
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(bool $isPrivate): self
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }
}
