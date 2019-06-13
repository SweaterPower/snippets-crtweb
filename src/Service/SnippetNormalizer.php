<?php

namespace App\Service;

use App\Entity\Snippet;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class SnippetNormalizer implements NormalizerInterface
{

    private $normalizer;

    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
        $encoder = new JsonEncoder();
        $serializer = new Serializer([$normalizer], [$encoder]);
        $this->normalizer->setSerializer($serializer);
    }

    public function normalize($topic, $format = null, array $context = [])
    {
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getId();
            },
        ];
        $data = $this->normalizer->normalize($topic, $format, $defaultContext);

        $data['owner']['emailRequestDatetime'] = $data['owner']['emailRequestDatetime']['timestamp'];
        $data['owner'] = ['id' => $data['owner']['id'], 'username' => $data['owner']['username'], 'email' => $data['owner']['email']];

        return $data;
    }
    
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Snippet;
    }

}
