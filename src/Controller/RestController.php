<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use App\Entity\Snippet;
use App\Form\ApiSnippetFormType;
use App\Service\RandomTokenGenerator;
use App\Entity\User;
use App\Entity\UserRole;

/**
 * Контроллер для доступа к сниппетам с помощью REST API
 * @Route("/api", name="api_")
 */
class RestController extends FOSRestController
{

    /**
     * Выдает пользователю токен доступа
     * @Rest\Get("/login")
     *
     * @return Response
     */
    public function getAccessTokenAction(Request $request, RandomTokenGenerator $generator)
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user !== null)
        {
            $manager = $this->getDoctrine()->getManager();
            $token = $generator->getToken();
            $userRole = $manager->getRepository(UserRole::class)->getRoleAPI();
            
            if ($userRole !== null)
            {
                $user->addRole($userRole);
                $user->setApiToken($token);
                $manager->flush();
                
                return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_OK)->setTemplate('api/result.html.twig')->setTemplateVar('status'));
            }
            return $this->handleView($this->view(['status' => 'not ok: 501'], Response::HTTP_NOT_IMPLEMENTED)->setTemplate('api/result.html.twig')->setTemplateVar('status'));
        }
        return $this->handleView($this->view(['status' => 'not ok: 500'], Response::HTTP_INTERNAL_SERVER_ERROR)->setTemplate('api/result.html.twig')->setTemplateVar('status'));
    }

    /**
     * Возвращает список сниппетов
     * @Rest\Get("/snippets")
     *
     * @return Response
     */
    public function getSnippetsAction()
    {
        $repository = $this->getDoctrine()->getRepository(Snippet::class);
        $snippets = $repository->findall();
        return $this->handleView($this->view($snippets)->setTemplate('api/listAll.html.twig')->setTemplateVar('snippets'));
    }

    /**
     * Создает сниппет
     * @Rest\Post("/add")
     *
     * @return Response
     */
    public function postSnippetAction(Request $request, RandomTokenGenerator $generator)
    {
        $snippet = new Snippet();
        $form = $this->createForm(ApiSnippetFormType::class, $snippet);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);
        if ($form->isSubmitted() && $form->isValid()) {
            $code = $generator->getToken();
            $snippet->setOwner($this->getDoctrine()->getRepository(User::class)->find(1));
            $snippet->setUrlCode($code);
            $em = $this->getDoctrine()->getManager();
            $em->persist($snippet);
            $em->flush();
            return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_CREATED)->setTemplate('api/result.html.twig')->setTemplateVar('status'));
        }
        return $this->handleView($this->view($form)->setTemplate('api/form.html.twig')->setTemplateVar('form'));
    }

}
