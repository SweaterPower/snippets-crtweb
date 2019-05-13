<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SnippetsController extends AbstractController 
{
    /**
     * @Route("/snippets", name="app_snippets")
     */
    public function login(): Response
    {
        return $this->render('snippets/list.html.twig');
    }
}