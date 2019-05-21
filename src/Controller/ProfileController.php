<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ResetEmailFormType;
use App\Form\ResetPasswordFormType;
use Swift_Mailer;
use App\Service\RandomTokenGenerator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\User;

/**
 *  Контроллер для операций с данными учетной записи пользователя
 */
class ProfileController extends AbstractController
{
   /**
     * Смена адреса электронной почты
     * 
     * @Route("/change/email", name="app_change_email")
     */
    public function changeEmail(Request $request, Swift_Mailer $mailer, RandomTokenGenerator $generator): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ResetEmailFormType::class);
        $manager = $this->getDoctrine()->getManager();

        $form->handleRequest($request);

        if ($user !== null && $form->isSubmitted() && $form->isValid()) {
            $token = $generator->getToken();
            $user->updateEmailToken($token);
            $newEmail = $form->get('email')->getData();
            
            $this->sendConfirtamionEmail('app_confirm_email',
                [
                    'userId' => $user->getId(),
                    'confirmToken' => $user->getEmailRequestToken(),
                    'newEmail' => $newEmail,
                ],
                $newEmail, $mailer);

            $manager->flush();

            return $this->render('profile/confirm.html.twig');
        }

        return $this->render('profile/change.html.twig', [
                'form' => $form->createView(),
                'title' => 'Change email',
        ]);
    }

    /**
     * Смена пароля
     * 
     * @Route("/change/password", name="app_change_password")
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ResetPasswordFormType::class);
        $manager = $this->getDoctrine()->getManager();

        $form->handleRequest($request);

        if ($user !== null && $form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $manager->flush();

            return $this->redirectToRoute('app_snippets_profile');
        }

        return $this->render('profile/change.html.twig', [
                'form' => $form->createView(),
                'title' => 'Change password',
        ]);
    }

    /**
     * Подтверждение смены адреса электронной почты
     * 
     * @Route("/confirm/email/{userId}/{confirmToken}/{newEmail}", name="app_confirm_email")
     */
    public function confirmChangeEmail(int $userId, string $confirmToken, string $newEmail): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $userRepo = $manager->getRepository(User::class);

        $user = $userRepo->find($userId);
        $tokenTTL = $this->getParameter('token_ttl');

        if ($user !== null && $user->getEmailRequestToken() == $confirmToken && $user->getConfirmTokenLifetime() <= $tokenTTL) {
            $user->setEmailRequestToken('');
            $user->setEmail($newEmail);

            $manager->flush();

            return $this->redirectToRoute('app_snippets_profile');
        } else {
            return $this->render('confirmation/expired.html.twig');
        }
    }

    /**
     * Восстановление пароля
     * 
     * @Route("/reset/password", name="app_reset_password")
     */
    public function resetPassword(Request $request, RandomTokenGenerator $generator, Swift_Mailer $mailer): Response
    {
        $form = $this->createForm(ResetEmailFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $email = $form->get('email')->getData();
            $user = $manager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($user !== null) {
                $token = $generator->getToken();
                $user->updateEmailToken($token);

                $this->sendConfirtamionEmail('app_confirm_password',
                    [
                        'userId' => $user->getId(),
                        'confirmToken' => $user->getEmailRequestToken(),
                    ],
                    $email, $mailer);

                $manager->flush();

                return $this->render('profile/confirm.html.twig');
            } else {
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('profile/change.html.twig', [
                'form' => $form->createView(),
                'title' => 'Enter your email',
        ]);
    }

    /**
     * Подтверждение восстановления пароля
     * 
     * @Route("/confirm/password/{userId}/{confirmToken}", name="app_confirm_password")
     */
    public function confirmResetPassword(Request $request, int $userId, string $confirmToken, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $userRepo = $manager->getRepository(User::class);

        $user = $userRepo->find($userId);
        $tokenTTL = $this->getParameter('token_ttl');

        if ($user !== null && $user->getEmailRequestToken() == $confirmToken && $user->getConfirmTokenLifetime() <= $tokenTTL) {
            $form = $this->createForm(ResetPasswordFormType::class);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $user->setEmailRequestToken('');
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $manager->flush();

                return $this->render('confirmation/confirmed.html.twig', [
                        'user' => $user,
                        'confirmToken' => $confirmToken,
                ]);
            }

            return $this->render('profile/change.html.twig', [
                    'form' => $form->createView(),
                    'title' => 'New password',
            ]);
        } else {
            return $this->render('confirmation/expired.html.twig');
        }
    }
    
    /**
     * Отправляет на почту сообщение для подтверждения почты
     * 
     * @param Swift_Mailer $mailer
     * @param User $user
     */
    private function sendConfirtamionEmail(string $targetRoute, array $params, string $recipient, Swift_Mailer $mailer): void
    {
        $url = $this->generateUrl($targetRoute, $params, UrlGeneratorInterface::ABSOLUTE_URL);

        $message = (new \Swift_Message('Confirm email'))
            ->setFrom('send@snpcrt.com')
            ->setTo($recipient)
            ->setBody($this->renderView(
                'confirmation/message.html.twig',
                [
                    'url' => $url,
            ]),
            'text/html');
        $mailer->send($message);
    }
}
