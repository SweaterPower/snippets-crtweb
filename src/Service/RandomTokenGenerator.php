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
        return rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
    }
}
