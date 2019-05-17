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
use App\Service\RandomTokenGenerator;
use Knp\Bundle\PaginatorBundle\KnpPaginatorBundle;
use Knp\Component\Pager\PaginatorInterface;

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
    public function admin(Request $request, PaginatorInterface $paginator): Response
    {
        $manager = $this->getDoctrine()->getManager();

        $query = $manager->getRepository(Snippet::class)->getFindAllQueryBuilder();
        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            5 /* limit per page */
        );

        return $this->render('snippets/list.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Добавление нового сниппета
     * 
     * @Route("/snippets/add", name="app_snippets_add")
     */
    public function add(Request $request, RandomTokenGenerator $generator): Response
    {
        $snippet = new Snippet();
        $form = $this->createForm(SnippetFormType::class, $snippet);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $code = $generator->getToken();

            $snippet->setOwner($this->getUser());
            $snippet->setUrlCode($code);

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
     * @Route("/snippets/detail/{urlCode}", name="app_snippets_detail")
     */
    public function item(Snippet $snippet): Response
    {
        if ($this->isGranted('view', $snippet)) {
            return $this->render('snippets/item.html.twig', [
                    'snippet' => $snippet,
            ]);
        } else {
            return $this->redirectToRoute('app_snippets_list');
        }
    }

    /**
     * Удаление сниппета
     * 
     * @Route("/snippets/delete/{urlCode}", name="app_snippets_delete")
     */
    public function delete(Snippet $snippet): Response
    {
        if ($this->isGranted('edit', $snippet)) {
            $manager = $this->getDoctrine()->getManager();
            $manager->remove($snippet);
            $manager->flush();
        }

        return $this->redirectToRoute('app_snippets_list');
    }

    /**
     * Редактирование сниппета
     * 
     * @Route("/snippets/edit/{urlCode}", name="app_snippets_edit")
     */
    public function edit(Snippet $snippet, Request $request): Response
    {
        if ($this->isGranted('edit', $snippet)) {
            $manager = $this->getDoctrine()->getManager();
            $form = $this->createForm(SnippetFormType::class, $snippet);

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $manager->flush();

                return $this->redirectToRoute('app_snippets_list');
            }

            return $this->render('snippets/form.html.twig', [
                    'form' => $form->createView(),
            ]);
        } else {
            return $this->redirectToRoute('app_snippets_detail', ['urlCode' => $snippet->getUrlCode()]);
        }
    }

    /**
     * Вывод списка сниппетов, принадлежащих текущему пользователю
     * 
     * @Route("/snippets/profile/", name="app_snippets_profile")
     */
    public function profile(Request $request, PaginatorInterface $paginator)
    {
        $manager = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $query = $manager->getRepository(Snippet::class)->getFindByOwnerQueryBuilder($user->getId());

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            5 /* limit per page */
        );

        return $this->render('snippets/list.html.twig', ['pagination' => $pagination]);
    }

    /**
     * Вывод списка публичных сниппетов
     * 
     * @Route("/snippets/", name="app_snippets_list")
     */
    public function list(Request $request, PaginatorInterface $paginator)
    {
        $repo = $this->getDoctrine()->getManager()->getRepository(Snippet::class);
        $user = $this->getUser();
        $query = null;
        if ($user) {
            $query = $repo->getFindPublicAndOwnQueryBuilder($user->getId());
        } else {
            $query = $repo->getFindPublicOnlyQueryBuilder();
        }

        $pagination = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1), /* page number */
            5 /* limit per page */
        );

        return $this->render('snippets/list.html.twig', ['pagination' => $pagination]);
    }

}
