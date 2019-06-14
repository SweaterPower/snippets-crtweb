<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Service\TokenManager;
use App\Entity\User;
use App\Entity\UserRole;

/**
 * Воутер для проверки действительности токена API
 */
class ApiVoter extends Voter
{
    const TOKEN = 'api_token';
    
    private $tokenManager;
    
    public function __construct(TokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }

    protected function supports($attribute, $subject)
    {
        if ($attribute != self::TOKEN) {
            return false;
        }

        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->tokenManager->isUserTokenValid($subject);
    }
}
