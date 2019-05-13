<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use VK\OAuth\VKOAuth;
use VK\OAuth\VKOAuthDisplay;
use VK\OAuth\Scopes\VKOAuthUserScope;
use VK\OAuth\VKOAuthResponseType;
use App\Repository\UserRepository;
use App\Repository\UserStatusRepository;
use App\Security\LoginFormAuthenticator;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

/**
 * Контроллер для регистрации пользователей.
 */
class VkAuthController extends AbstractController
{

    /**
     * @Route("/vk/gettoken", name="app_vktoken")
     */
    public function getToken(): Response
    {
        $oauth = new VKOAuth();
        $client_id = 6957645;
        $redirect_uri = 'http://localhost/login';
        $display = VKOAuthDisplay::PAGE;
        $scope = array(VKOAuthUserScope::EMAIL);
        $state = 'I0RN1xpHiyOCE0Yplo0T';
        $revoke_auth = true;

        $browser_url = $oauth->getAuthorizeUrl(VKOAuthResponseType::TOKEN, $client_id, $redirect_uri, $display, $scope, $state, null, $revoke_auth);
        return $this->redirect($browser_url);
    }

    /**
     * @Route("/vk/login", name="app_vklogin")
     */
    public function login(
        Request $request, LoginFormAuthenticator $authenticator, GuardAuthenticatorHandler $guardHandler,
        UserRepository $userRepo, UserStatusRepository $userStatusRepo): Response
    {//#access_token={}&expires_in={}&user_id={$uid}&$email={$email}&state={}
        echo var_dump($request);
        $email = $request->get('email');
        $uid = $request->get('user_id');
        $entityManager = $this->getDoctrine()->getManager();
        
        $user = $userRepo->findOneBy(['VkontakteId' => $uid]);
        if (!$user) {

            $user = $userRepo->findOneBy(['email' => $email]);

            if (!$user) {
                $user = new User();
                $user->setEmail($email);
                $user->setStatus($userStatusRepo->findOneBy(['code' => 'active']));
                $user->setVkontakteId($uid);
                $entityManager->persist($user);
            }
            else {
                $user->setVkontakteId($uid);
            }
            
            $entityManager->flush();
        }

        return $guardHandler->authenticateUserAndHandleSuccess(
                $user, // the User object you just created
                $request,
                $authenticator, // authenticator whose onAuthenticationSuccess you want to use
                'main'          // the name of your firewall in security.yaml
        );
    }

}
