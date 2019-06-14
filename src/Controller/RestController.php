<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
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
use App\Service\TokenManager;
use App\Entity\User;
use App\Entity\UserRole;

/**
 * TODO:
 * Таблицу с токенами (user_id, token, createdDateTime)
 * Проверку на время жизни токена
 * Если нет токена, аутентификация по логину паролю
 * Сервис для выдачи токена
 * Запрос на обновление токена
 * Возвращать логичные коды ошибок
 * Вместе со сниппетом передавать права доступа к нему
 * 
 * Контроллер для доступа к сниппетам с помощью REST API
 * @Route("/api", name="api_")
 */
class RestController extends FOSRestController
{

    /**
     * Вовзращает пользователю токен доступа после аутентификации по логину и паролю
     * @Rest\Post("/login")
     *
     * @return Response
     */
    public function postLoginAction(Request $request)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('api_token', $user);
        return new Response('{"success":"true"}', Response::HTTP_OK, ['content-type' => 'application/json', 'SNP-AUTH-TOKEN' => $user->getApiToken()]);
    }

    /**
     * Генерирует для пользователя новый токен доступа
     * @Rest\Post("/refresh")
     *
     * @return Response
     */
    public function getAccessTokenAction(Request $request, TokenManager $manager)
    {
        $user = $this->getUser();
        $this->denyAccessUnlessGranted('api_token', $user);
        $manager->generateNewToken($user);
        return new Response('{"success":"true"}', Response::HTTP_OK, ['content-type' => 'application/json', 'SNP-AUTH-TOKEN' => $user->getApiToken()]);
    }

    /**
     * Возвращает список сниппетов
     * @Rest\Get("/snippets")
     *
     * @return Response
     */
    public function getSnippetsAction(SnippetNormalizer $normalizer)
    {
        $this->denyAccessUnlessGranted('api_token', $this->getUser());
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
        return new Response("{'message':'snippets not found'}", Response::HTTP_NOT_FOUND, ['content-type' => 'application/json']);
    }

    /**
     * Возвращает существующий сниппет
     * @Rest\Get("/snippet")
     *
     * @return Response
     */
    public function getSnippetAction(Request $request, SnippetNormalizer $normalizer)
    {
        $this->denyAccessUnlessGranted('api_token', $this->getUser());
        $message = '';
        $code = Response::HTTP_INTERNAL_SERVER_ERROR;
        $data = json_decode($request->getContent(), true);
        $urlCode = $data['urlCode'];
        if ($urlCode !== null) {
            $repository = $this->getDoctrine()->getRepository(Snippet::class);
            $snippet = $repository->findOneBy(['urlCode' => $urlCode]);
            if ($snippet !== null) {
                if ($this->isGranted('view', $snippet)) {
                    $serializer = new Serializer([$normalizer], [new JsonEncoder()]);
                    return new Response(
                        $serializer->serialize(
                            $snippet,
                            'json'),
                        Response::HTTP_OK,
                        ['content-type' => 'application/json']
                    );
                } else {
                    $code = Response::HTTP_FORBIDDEN;
                    $message = 'access denied';
                }
            } else {
                $code = Response::HTTP_NOT_FOUND;
                $message = 'snippet not found';
            }
        } else {
            $code = Response::HTTP_BAD_REQUEST;
            $message = 'bad request data';
        }
        return new Response(
            '{"success":"false", "message":"' . $message . '"}',
            $code,
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
        $this->denyAccessUnlessGranted('api_token', $this->getUser());
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
        $this->denyAccessUnlessGranted('api_token', $this->getUser());
        $message = '';
        $code = Response::HTTP_INTERNAL_SERVER_ERROR;
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
                    $code = Response::HTTP_FORBIDDEN;
                    $message = 'access denied';
                }
            } else {
                $code = Response::HTTP_NOT_FOUND;
                $message = 'snippet not found';
            }
        } else {
            $code = Response::HTTP_BAD_REQUEST;
            $message = 'bad request data';
        }
        return new Response(
            '{"success" : "false", "message": "' . $message . '"}',
            $code,
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
        $this->denyAccessUnlessGranted('api_token', $this->getUser());
        $message = '';
        $code = Response::HTTP_INTERNAL_SERVER_ERROR;
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
                    $code = Response::HTTP_FORBIDDEN;
                    $message = 'access denied';
                }
            } else {
                $code = Response::HTTP_NOT_FOUND;
                $message = 'snippet not found';
            }
        } else {
            $code = Response::HTTP_BAD_REQUEST;
            $message = 'bad request data';
        }
        return new Response(
            '{"success" : "false", "message": "' . $message . '"}',
            $code,
            ['content-type' => 'application/json']
        );
    }

}
