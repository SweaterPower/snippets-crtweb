<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use App\Service\SnippetNormalizer;
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
     * Генерирует для пользователя токен доступа
     * @Rest\Get("/login")
     *
     * @return Response
     */
    public function postAccessTokenAction(Request $request, RandomTokenGenerator $generator)
    {
        $message = '';
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user !== null) {
            $manager = $this->getDoctrine()->getManager();
            $token = $generator->getToken();
            $userRole = $manager->getRepository(UserRole::class)->getRoleAPI();

            if ($userRole !== null) {
                $user->addRole($userRole);
                $user->setApiToken($token);
                $manager->flush();

                return new Response('{"success":"true"}', Response::HTTP_OK, ['content-type' => 'application/json', 'SNP-AUTH-TOKEN' => $token]);
            }
            else {
                $message = "authorization error";
            }
        } else {
            $message = "authentication failed";
        }
        return new Response('{"success":"false", "message":"' . $message . '"}', Response::HTTP_INTERNAL_SERVER_ERROR, ['content-type' => 'application/json']);
    }

    /**
     * Возвращает список сниппетов
     * @Rest\Get("/snippets")
     *
     * @return Response
     */
    public function getSnippetsAction(SnippetNormalizer $normalizer)
    {
        $repository = $this->getDoctrine()->getRepository(Snippet::class);
        $snippets = $repository->findPublicAndOwn($this->getUser());

        if ($snippets !== null) {
            $serializer = new Serializer([$normalizer], [new JsonEncoder()]);
            return new Response(
                $serializer->serialize(
                    $snippets,
                    'json'),
                Response::HTTP_OK,
                ['content-type' => 'application/json']
            );
        }
        return new Response("{'message':'snippets not found'}", Response::HTTP_INTERNAL_SERVER_ERROR, ['content-type' => 'application/json']);
    }

    /**
     * Возвращает существующий сниппет
     * @Rest\Get("/snippet")
     *
     * @return Response
     */
    public function getSnippetAction(Request $request, SnippetNormalizer $normalizer)
    {
        $message = '';
        $data = json_decode($request->getContent(), true);
        $urlCode = $data['urlCode'];
        if ($urlCode !== null) {
            $repository = $this->getDoctrine()->getRepository(Snippet::class);
            $snippet = $repository->findOneBy(['urlCode' => $urlCode]);
            if ($snippet !== null) {
                if ($this->isGranted('view', $snippet)) {
                    $serializer = new Serializer([$normalizer], [new JsonEncoder()]);
                    return new Response(
                        '{"success":"true","data":"' .
                        $serializer->serialize(
                            $snippet,
                            'json')
                        . '"}',
                        Response::HTTP_OK,
                        ['content-type' => 'application/json']
                    );
                } else {
                    $message = 'access denied';
                }
            } else {
                $message = 'snippet not found';
            }
        } else {
            $message = 'bad request data';
        }
        return new Response(
            '{"success":"false", "message":"' . $message . '"}',
            Response::HTTP_INTERNAL_SERVER_ERROR,
            ['content-type' => 'application/json']
        );
    }

    /**
     * Создание сниппета
     * @Rest\Post("/snippet")
     *
     * @return Response
     */
    public function postSnippetAction(Request $request, RandomTokenGenerator $generator, SnippetNormalizer $normalizer)
    {
        $data = $request->getContent();
        $serializer = new Serializer([new GetSetMethodNormalizer()], [new JsonEncoder()]);
        $snippet = $serializer->deserialize($data, Snippet::class, 'json');

        $code = $generator->getToken();
        $snippet->setOwner($this->getUser());
        $snippet->setUrlCode($code);
        $em = $this->getDoctrine()->getManager();
        $em->persist($snippet);
        $em->flush();
        return new Response(
            '{"success" : "true", "message": "snippet added"}',
            Response::HTTP_OK,
            ['content-type' => 'application/json']
        );
    }

    /**
     * Редактирование сниппета
     * @Rest\Put("/snippet")
     *
     * @return Response
     */
    public function putSnippetAction(Request $request)
    {
        $message = '';
        $data = $request->getContent();
        $urlCode = json_decode($data, true)['urlCode'];
        if ($urlCode !== null) {
            $repository = $this->getDoctrine()->getRepository(Snippet::class);
            $snippet = $repository->findOneBy(['urlCode' => $urlCode]);
            if ($snippet !== null) {
                if ($this->isGranted('edit', $snippet)) {
                    $serializer = new Serializer([new GetSetMethodNormalizer()], [new JsonEncoder()]);
                    $serializer->deserialize($data, Snippet::class, 'json', ['object_to_populate' => $snippet]);

                    $em = $this->getDoctrine()->getManager();
                    $em->flush();
                    return new Response(
                        '{"success" : "true", "message": "snippet modified"}',
                        Response::HTTP_OK,
                        ['content-type' => 'application/json']
                    );
                } else {
                    $message = 'access denied';
                }
            } else {
                $message = 'snippet not found';
            }
        } else {
            $message = 'bad request data';
        }
        return new Response(
            '{"success" : "false", "message": "' . $message . '"}',
            Response::HTTP_INTERNAL_SERVER_ERROR,
            ['content-type' => 'application/json']
        );
    }

    /**
     * Удаление сниппета
     * @Rest\Delete("/snippet")
     *
     * @return Response
     */
    public function deleteSnippetAction(Request $request)
    {
        $message = '';
        $data = $request->getContent();
        $urlCode = json_decode($data, true)['urlCode'];
        if ($urlCode !== null) {
            $repository = $this->getDoctrine()->getRepository(Snippet::class);
            $snippet = $repository->findOneBy(['urlCode' => $urlCode]);
            if ($snippet !== null) {
                if ($this->isGranted('edit', $snippet)) {
                    $em = $this->getDoctrine()->getManager();
                    $em->remove($snippet);
                    $em->flush();
                    return new Response(
                        '{"success" : "true", "message": "snippet deleted"}',
                        Response::HTTP_OK,
                        ['content-type' => 'application/json']
                    );
                } else {
                    $message = 'access denied';
                }
            } else {
                $message = 'snippet not found';
            }
        } else {
            $message = 'bad request data';
        }
        return new Response(
            '{"success" : "false", "message": "' . $message . '"}',
            Response::HTTP_INTERNAL_SERVER_ERROR,
            ['content-type' => 'application/json']
        );
    }

}
