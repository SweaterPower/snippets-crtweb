<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Form\RegistrationFormType;
use App\Form\ResetEmailFormType;
use App\Form\ResetPasswordFormType;
use App\Entity\User;
use App\Entity\UserStatus;
use App\Entity\UserRole;
use Swift_Mailer;
use App\Service\RandomTokenGenerator;

/**
 * Контроллер для регистрации, подтверждения регистрации и логина пользователей.
 */
class SecurityController extends AbstractController
{

    /**
     * Аутентификация пользователя по логину и паролю
     * 
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * Проверка токена и TTL при подтверждении регистрации пользователя
     * 
     * @Route("/confirm/{userId}/{confirmToken}", name="app_confirmation")
     */
    public function confirm(int $userId, string $confirmToken)
    {
        $manager = $this->getDoctrine()->getManager();
        $userRepo = $manager->getRepository(User::class);
        $userStatusRepo = $manager->getRepository(UserStatus::class);

        $user = $userRepo->find($userId);
        $userStatus = $userStatusRepo->getActiveStatus();
        $tokenTTL = $this->getParameter('token_ttl');

        if ($user !== null && $userStatus !== null && $user->getEmailRequestToken() == $confirmToken && $user->getConfirmTokenLifetime() <= $tokenTTL) {
            $user->setStatus($userStatus);
            $user->setEmailRequestToken('');

            $manager->flush();

            return $this->render('confirmation/confirmed.html.twig', [
                    'user' => $user,
                    'confirmToken' => $confirmToken,
            ]);
        } else {
            $manager->refresh();
            return $this->render('confirmation/expired.html.twig');
        }
    }

    /**
     * Регистрация пользователя
     * 
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, Swift_Mailer $mailer, RandomTokenGenerator $generator): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            $userRole = $manager->getRepository(UserRole::class)->getRoleUser();
            $userStatus = $manager->getRepository(UserStatus::class)->getNotConfirmedStatus();
            $token = $generator->getToken();

            if ($userRole !== null && $userStatus !== null) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $user->addRole($userRole);
                $user->setStatus($userStatus);
                $user->updateEmailToken($token);

                $manager->persist($user);
                $manager->flush();

                $this->sendConfirtamionEmail('app_confirmation',
                    [
                        'userId' => $user->getId(),
                        'confirmToken' => $user->getEmailRequestToken(),
                    ],
                    $user->getEmail(), $mailer);

                return $this->render('registration/confirm.html.twig');
            }
        }

        return $this->render('registration/register.html.twig', [
                'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * Смена адреса электронной почты
     * 
     * @Route("/change/email", name="app_change_email")
     */
    private function changeEmail(Request $request, Swift_Mailer $mailer, RandomTokenGenerator $generator)
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
    private function changePassword(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $this->getUser();
        $form = $this->createForm(ResetEmailFormType::class);
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
    private function confirmChangeEmail(int $userId, string $confirmToken, string $newEmail)
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
    private function resetPassword(RandomTokenGenerator $generator, Swift_Mailer $mailer)
    {
        $user = $this->getUser();
        if ($user !== null) {
            $manager = $this->getDoctrine()->getManager();
            $token = $generator->getToken();
            $user->updateEmailToken($token);

            $this->sendConfirtamionEmail('app_confirm_password',
                [
                    'userId' => $user->getId(),
                    'confirmToken' => $user->getEmailRequestToken(),
                ],
                $user->getEmail(), $mailer);

            $manager->flush();

            return $this->render('profile/confirm.html.twig');
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    /**
     * Подтверждение восстановления пароля
     * 
     * @Route("/confirm/password/{userId}/{confirmToken}", name="app_confirm_password")
     */
    private function confirmResetPassword(int $userId, string $confirmToken, UserPasswordEncoderInterface $passwordEncoder)
    {
        $manager = $this->getDoctrine()->getManager();
        $userRepo = $manager->getRepository(User::class);

        $user = $userRepo->find($userId);
        $tokenTTL = $this->getParameter('token_ttl');

        if ($user !== null && userStatus !== null && $user->getEmailRequestToken() == $confirmToken && $user->getConfirmTokenLifetime() <= $tokenTTL) {
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
