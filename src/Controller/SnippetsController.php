<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Snippet;
use App\Form\SnippetFormType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Контроллер для просмотра списка, редактирования, добавления и удаления сниппетов.
 */
class SnippetsController extends AbstractController
{

    /**
     * Вывод всех сниппетов
     * 
     * @Route("/admin", name="app_snippets_admin")
     */
    public function admin(): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $snippets = $manager->getRepository(Snippet::class)->findAll();

        return $this->render('snippets/list.html.twig', ['snippets' => $snippets]);
    }

    /**
     * Добавление нового сниппета
     * 
     * @Route("/snippets/add", name="app_snippets_add")
     */
    public function add(Request $request, Security $security): Response
    {
        $snippet = new Snippet();
        $form = $this->createForm(SnippetFormType::class, $snippet);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $user = $security->getUser();

            $snippet->setOwner($user);
            $snippet->generateUrlCode();

            $manager->persist($snippet);
            $manager->flush();

            return $this->redirectToRoute('app_snippets_list');
        }

        return $this->render('snippets/form.html.twig', [
                'form' => $form->createView(),
        ]);
    }

    /**
     * Вывод отдельного сниппета
     * 
     * @Route("/snippets/detail/code={code}", name="app_snippets_detail")
     */
    public function item(string $code): Response
    {
        $snippet = $this->getDoctrine()->getManager()->getRepository(Snippet::class)->findOneBy(["urlCode" => $code]);
        return $this->render('snippets/item.html.twig', [
                'snippet' => $snippet,
        ]);
    }

    /**
     * Удаление сниппета
     * 
     * @Route("/snippets/delete/code={code}", name="app_snippets_delete")
     */
    public function delete(string $code): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $snippet = $manager->getRepository(Snippet::class)->findOneBy(["urlCode" => $code]);
        $manager->remove($snippet);
        $manager->flush();

        return $this->redirectToRoute('app_snippets_list');
    }

    /**
     * Редактирование сниппета
     * 
     * @Route("/snippets/edit/code={code}", name="app_snippets_edit")
     */
    public function edit(string $code, Request $request): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $snippet = $manager->getRepository(Snippet::class)->findOneBy(["urlCode" => $code]);

        $form = $this->createForm(SnippetFormType::class, $snippet);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            return $this->redirectToRoute('app_snippets_list');
        }

        return $this->render('snippets/form.html.twig', [
                'form' => $form->createView(),
        ]);
    }

    /**
     * Вывод списка сниппетов, принадлежащих текущему пользователю
     * 
     * @Route("/snippets/profile/", name="app_snippets_profile")
     */
    public function profile(Security $security)
    {
        $manager = $this->getDoctrine()->getManager();
        $user = $security->getUser();
        $snippets = $manager->getRepository(Snippet::class)->findByOwner($user->getId());

        return $this->render('snippets/list.html.twig', ['snippets' => $snippets]);
    }

    /**
     * Вывод списка публичных сниппетов
     * 
     * @Route("/snippets/", name="app_snippets_list")
     */
    public function list()
    {
        $manager = $this->getDoctrine()->getManager();
        $snippets = $manager->getRepository(Snippet::class)->getPublicOnly();

        return $this->render('snippets/list.html.twig', ['snippets' => $snippets]);
    }

}
