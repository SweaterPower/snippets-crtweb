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

class SecurityController extends AbstractController
{
    //время жизни токена подтверждения электронной почты
    private $tokenTTL = 5;
    
    /**
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
     * @Route("/confirm/user={userId}/token={confirmToken}", name="app_confirmation")
     */
    public function confirm(int $userId, string $confirmToken)
    {
        $manager = $this->getDoctrine()->getManager();
        $userRepo = $manager->getRepository(User::class);
        $userStatusRepo = $manager->getRepository(UserStatus::class);

        $user = $userRepo->findOneBy(['id' => $userId]);
        $userStatus = $userStatusRepo->findOneBy(['code' => 'active']);

        if ($user !== null && $userStatus !== null && $user->getEmailRequestToken() == $confirmToken && $user->getConfirmTokenLifetime() <= $this->tokenTTL) {
            $user->setStatus($userStatus);
            $user->eraseConfirmToken();

            $manager->flush();

            return $this->render('confirmation/confirmed.html.twig', [
                  'userId' => $userId,
                  'userName' => $user->getUsername(),
                  'confirmToken' => $confirmToken,
            ]);
        } else {
            return $this->render('confirmation/expired.html.twig');
        }
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, Swift_Mailer $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            $userRole = $manager->getRepository(UserRole::class)->findOneBy(['code' => 'user']);
            $userStatus = $manager->getRepository(UserStatus::class)->findOneBy(['code' => 'not_confirmed']);

            if ($userRole !== null && $userStatus !== null) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
                $user->setRole($userRole);
                $user->setStatus($userStatus);
                $user->generateEmailToken();

                $manager->persist($user);
                $manager->flush();

                $url = $this->generateUrl('app_confirmation',
                    [
                      'userId' => $user->getId(),
                      'confirmToken' => $user->getEmailRequestToken(),
                    ],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );

                $message = (new \Swift_Message('Confirm email'))
                    ->setFrom('send@snpcrt.com')
                    ->setTo($user->getEmail())
                    ->setBody($this->renderView(
                        'confirmation/message.html.twig',
                        [
                          'url' => $url,
                    ]),
                    'text/html');
                $mailer->send($message);

                return $this->render('registration/confirm.html.twig');
            }
        }

        return $this->render('registration/register.html.twig', [
              'registrationForm' => $form->createView(),
        ]);
    }

}
