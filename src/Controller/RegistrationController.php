<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Repository\UserStatusRepository;
use App\Repository\UserRoleRepository;
use Swift_Mailer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Контроллер для регистрации пользователей.
 */
class RegistrationController extends AbstractController
{

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, Swift_Mailer $mailer, UserRoleRepository $roleRepo, UserStatusRepository $statusRepo): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setEmail($form->get('email')->getData());
            $user->setUsername($form->get('username')->getData());
            $user->setRole($roleRepo->findOneBy(['code' => 'admin']));
            $user->setStatus($statusRepo->findOneBy(['code' => 'not_confirmed']));
            $user->generateEmailToken();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $url = $this->generateUrl('confirmation',
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

            return $this->redirectToRoute('_profiler_home');
        }

        return $this->render('registration/register.html.twig', [
              'registrationForm' => $form->createView(),
        ]);
    }

}
