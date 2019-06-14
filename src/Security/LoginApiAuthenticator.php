<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use App\Service\TokenManager;
use App\Entity\UserStatus;

/**
 * Аутентифицирует пользователя по логину и паролю и выдает ему токен API
 * Срабатывает только в том случае, если пользователь не прикрепил к заголовку запроса токен API
 */
class LoginApiAuthenticator extends AbstractGuardAuthenticator
{
    private $em;
    private $passwordEncoder;
    private $tokenManager;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, TokenManager $tokenManager)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenManager = $tokenManager;
    }

    public function supports(Request $request)
    {
        return $request->request->has('email') && $request->request->has('password');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
        ];
        
        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $user = $userProvider->loadUserByUsername($credentials['email']);
        
        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Email could not be found.');
        }
        
        if ($user->getStatus() != $this->em->getRepository(UserStatus::class)->getActiveStatus()) {
            throw new CustomUserMessageAuthenticationException('Email address not confirmed.');
        }
        
        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $user = $token->getUser();
        if ($user !== null && $user instanceof User) {
            $this->tokenManager->generateNewToken($user);
        }
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            'message' => 'Authentication Required'
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
