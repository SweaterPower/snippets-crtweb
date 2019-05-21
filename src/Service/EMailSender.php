<?php

namespace App\Service;

use Swift_Mailer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Controller\SecurityController;

/**
 * Отправляет сообщения на электронную почту
 */
class EMailSender
{
//    private $mailer;
//    private $urlGen;
//    private $controller;
//    
//    public function __construct(Swift_Mailer $mailer, UrlGeneratorInterface $urlGen, SecurityController $controller)
//    {
//        $this->mailer = $mailer;
//        $this->urlGen = $urlGen;
//        $this->controller = $controller;
//    }
//    
//    /**
//     * Отправляет на почту сообщение для подтверждения почты
//     * 
//     * @param Swift_Mailer $mailer
//     */
//    public function sendConfirtamionEmail(string $targetRoute, array $params, string $recipient): void
//    {
//        $url = $this->urlGen->generate($targetRoute, $params, UrlGeneratorInterface::ABSOLUTE_URL);
//
//        $message = (new \Swift_Message('Confirm email'))
//            ->setFrom('send@snpcrt.com')
//            ->setTo($recipient)
//            ->setBody($this->controller->renderView(
//                'confirmation/message.html.twig',
//                [
//                    'url' => $url,
//            ]),
//            'text/html');
//        $this->mailer->send($message);
//    }
}
