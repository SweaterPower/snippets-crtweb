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

///DELET THIS!!!
use App\Entity\User;

/**
 * Контроллер для доступа к сниппетам с помощью REST API
 * @Route("/api", name="api_")
 */
class RestController extends FOSRestController
{

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
     * @Rest\Post("/snippet")
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
