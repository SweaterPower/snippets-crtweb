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
    public function confirm(int $userId, string $confirmToken): Response
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
