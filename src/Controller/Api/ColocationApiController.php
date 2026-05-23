<?php

namespace App\Controller\Api;

use App\Entity\Colocation;
use App\Repository\ColocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/colocations-custom', name: 'api_colocations_')]
class ColocationApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(ColocationRepository $repo, SerializerInterface $serializer): JsonResponse
    {
        $colocations = $repo->findAll();
        $json        = $serializer->serialize($colocations, 'json', ['groups' => 'colocation:read']);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_PROPRIETAIRE')]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        if (!str_contains($request->headers->get('Content-Type', ''), 'application/json')) {
            return $this->json(['error' => 'Content-Type application/json requis.'], Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }

        try {
            /** @var Colocation $col */
            $col = $serializer->deserialize($request->getContent(), Colocation::class, 'json', ['groups' => 'colocation:write']);
        } catch (\Exception) {
            return $this->json(['error' => 'JSON invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $col->setProprietaire($this->getUser());

        $errors = $validator->validate($col);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $messages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($col);
        $em->flush();
        $json = $serializer->serialize($col, 'json', ['groups' => 'colocation:read']);
        return new JsonResponse($json, Response::HTTP_CREATED, [], true);
    }
}
