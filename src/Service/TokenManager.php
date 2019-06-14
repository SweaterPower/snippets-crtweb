<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use DateTime;
use App\Service\RandomTokenGenerator;
use App\Entity\AccessToken;
use App\Entity\User;

/**
 * Проверяет действительность токенов доступа к API и выдает новые
 */
class TokenManager
{

    private $em;
    private $tokenTTL;
    private $generator;

    public function __construct(int $tokenTTL, EntityManagerInterface $em, RandomTokenGenerator $generator)
    {
        $this->tokenTTL = $tokenTTL;
        $this->em = $em;
        $this->generator = $generator;
    }

    /**
     * Время, прошедшее с момента создания токена (в минутах)
     */
    private function getTokenLifetime(AccessToken $token): int
    {
        $now = new DateTime('now');
        $time = (int) round(($now->getTimeStamp() - $token->getCreatedDateTime()->getTimestamp()) / 60);

        return $time;
    }

    /*
     * Проверяет, действителен ли токен
     */
    public function isUserTokenValid($user): bool
    {
        $repo = $this->em->getRepository(AccessToken::class);
        $token = $repo->findOneLatestForUser($user);
        if ($token === null)
            return false;

        return $this->tokenTTL >= $this->getTokenLifetime($token);
    }

    public function generateNewToken($user)
    {
        $token = $this->generator->getToken();

        $accessToken = new AccessToken();
        $accessToken->setToken($token);
        $accessToken->setUser($user);
        $accessToken->setCreatedDateTime(new DateTime('now'));
        $user->setApiToken($token);

        $this->em->persist($accessToken);
        $this->em->flush();
    }

}
