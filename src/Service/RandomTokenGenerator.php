<?php
namespace App\Service;

/*
 * Сервис для генерации случайных токенов
 */
class RandomTokenGenerator
{
    /**
     * Генерирует набор случайных символов
     */
    public function getToken(): string
    {
        return md5(random_bytes(32));
    }
}
