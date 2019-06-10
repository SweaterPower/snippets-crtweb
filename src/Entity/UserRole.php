<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Роль пользователя.
 * Позволяет регулировать, к каким разделам сайта пользователь имеет доступ.
 * 
 * @ORM\Entity(repositoryClass="App\Repository\UserRoleRepository")
 */
class UserRole
{
    const USER_ROLE_USER = 'ROLE_USER';
    const USER_ROLE_ADMIN = 'ROLE_ADMIN';
    const USER_ROLE_API = 'ROLE_API';
    
    /**
     * Уникальный идентификатор.
     * 
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Кодовое обозначение.
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $code;

    /**
     * Название роли.
     * 
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
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
}
