<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Repository\UserRepository;
use App\Repository\UserStatusRepository;
use App\Entity\User;

/**
 * Контроллер для подтверждения действий на аккаунте пользователя.
 */
class ConfirmationController extends AbstractController
{
    private $tokenTTL = 2;

    /**
     * @Route("/confirm/user={userId}/token={confirmToken}", name="app_confirmation")
     */
    public function confirm(int $userId, string $confirmToken, Request $request, UserRepository $userRepo, UserStatusRepository $userStatusRepo)
    {
        $user = $userRepo->findOneBy(['id' => $userId]);
        if ($user !== null) {
            if ($user->getEmailRequestToken() == $confirmToken) {
                if ($user->getConfirmTokenLifetime() <= $this->tokenTTL) {
                    $user->setStatus($userStatusRepo->findOneBy(['code' => 'active']));
                    $user->eraseConfirmToken();
                    
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->flush();
                    
                    return $this->render('confirmation/confirmed.html.twig', [
                          'userId' => $userId,
                          'userName' => $user->getUsername(),
                          'confirmToken' => $confirmToken,
                    ]);
                } else {
                    return $this->render('confirmation/expired.html.twig');
                }
            }
        }
        return $this->redirectToRoute('app_register');
    }

}
