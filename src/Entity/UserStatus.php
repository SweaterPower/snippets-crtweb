<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Статус пользователя.
 * Отображает прогресс в подтверждении регистрации по электронной почте.
 * 
 * @ORM\Entity(repositoryClass="App\Repository\UserStatusRepository")
 */
class UserStatus
{
    const ACTIVE_STATUS_CODE = 'active';
    const NOT_CONFIRMED_STATUS_CODE = 'not_confirmed';
    const NOT_ACTIVE_STATUS_CODE = 'not_active';
    
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
     * Название статуса.
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
