<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Snippet;
use App\Entity\AccessType;
use App\Form\SnippetFormType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Контроллер для просмотра списка, редактирования, добавления и удаления сниппетов.
 */
class SnippetsController extends AbstractController
{

    /**
     * Вывод списка сниппетов
     * 
     * @Route("/snippets", name="app_snippets")
     */
    public function list(): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $snippets = $manager->getRepository(Snippet::class)->findAll();
        
        return $this->render('snippets/list.html.twig', ['snippets' => $snippets]);
    }

    /**
     * Добавление нового сниппета
     * 
     * @Route("/snippets/add", name="app_addsnippet")
     */
    public function add(Request $request, Security $security): Response
    {
        $snippet = new Snippet();
        $form = $this->createForm(SnippetFormType::class, $snippet);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $statusCode = $form->get('private') ? 'private' : 'public';
            $user = $security->getUser();
            
            $snippet->setAccessType($manager->getRepository(AccessType::class)->findOneBy(['code' => $statusCode]));
            $snippet->setUser($user);
            $snippet->generateUrlCode();
            
            $manager->persist($snippet);
            $manager->flush();
            
            return $this->redirectToRoute('app_snippets');
        }

        return $this->render('snippets/form.html.twig', [
              'form' => $form->createView(),
        ]);
    }

    /**
     * Вывод сниппета
     * 
     * @Route("/snippets/detail/code={code}", name="app_snippetdetail")
     */
    public function item(string $code): Response
    {
        $snippet = $this->getDoctrine()->getManager()->getRepository(Snippet::class)->findOneBy(["urlCode" => $code]);
        return $this->render('snippets/detail.html.twig', [
              'snippet' => $snippet,
        ]);
    }

    /**
     * Удаление сниппета
     * 
     * @Route("/snippets/delete/code={code}", name="app_snippetdelete")
     */
    public function delete(string $code): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $snippet = $manager->getRepository(Snippet::class)->findOneBy(["urlCode" => $code]);
        $manager->remove($snippet);
        $manager->flush();
        
        return $this->redirectToRoute('app_snippets');
    }
    
    /**
     * Редактирование сниппета
     * 
     * @Route("/snippets/edit/code={code}", name="app_snippetedit")
     */
    public function edit(string $code, Request $request): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $snippet = $manager->getRepository(Snippet::class)->findOneBy(["urlCode" => $code]);
        
        $form = $this->createForm(SnippetFormType::class, $snippet);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $statusCode = $form->get('private') ? 'private' : 'public';
            $snippet->setAccessType($manager->getRepository(AccessType::class)->findOneBy(['code' => $statusCode]));
            $manager->flush();
            
            return $this->redirectToRoute('app_snippets');
        }

        return $this->render('snippets/form.html.twig', [
              'form' => $form->createView(),
        ]);
    }
}
