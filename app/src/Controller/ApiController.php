<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @TODO: features provided here (de-serializing, validation) need to be moved in some Listener/Subscriber
 */
abstract class ApiController extends AbstractController
{
    private SerializerInterface $serializer;

    private ValidatorInterface $validator;

    private Request $currentRequest;

    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        RequestStack $requestStack
    ) {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->currentRequest = $requestStack->getCurrentRequest() ?: Request::createFromGlobals();
    }

    protected function serializer(): SerializerInterface
    {
        return $this->serializer;
    }

    private function validate(object $object): void
    {
        $violations = $this->validator->validate($object, /* ['groups' => $validationGroups] */);

        if ($violations->count()) {
            // make sure to return a nicely formatted error
            throw new BadRequestHttpException('Validation error.');
        }
    }

    protected function deserialize(string $class): object
    {
        $content = $this->currentRequest->getContent();

        if (empty($content)) {
            throw new BadRequestHttpException('Empty request content provided!');
        }

        /** @var object $deserializedObject */
        $deserializedObject = $this->serializer->deserialize(
            $content,
            $class,
            'json',
            []
        );

        $this->validate($deserializedObject);

        return $deserializedObject;
    }
}
