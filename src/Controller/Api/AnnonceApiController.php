<?php

namespace App\Controller\Api;

use App\Entity\Annonce;
use App\Repository\AnnonceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/annonces-custom', name: 'api_annonces_')]
class AnnonceApiController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function index(AnnonceRepository $repo, SerializerInterface $serializer): JsonResponse
    {
        $annonces = $repo->findDisponibles();
        $json     = $serializer->serialize($annonces, 'json', ['groups' => 'annonce:read']);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Annonce $annonce, SerializerInterface $serializer): JsonResponse
    {
        $json = $serializer->serialize($annonce, 'json', ['groups' => 'annonce:read']);
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
            /** @var Annonce $annonce */
            $annonce = $serializer->deserialize($request->getContent(), Annonce::class, 'json', ['groups' => 'annonce:write']);
        } catch (\Exception) {
            return $this->json(['error' => 'JSON invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($annonce);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $messages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($annonce);
        $em->flush();
        $json = $serializer->serialize($annonce, 'json', ['groups' => 'annonce:read']);
        return new JsonResponse($json, Response::HTTP_CREATED, [], true);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_PROPRIETAIRE')]
    public function update(
        Annonce $annonce,
        Request $request,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        try {
            $serializer->deserialize($request->getContent(), Annonce::class, 'json', [
                'object_to_populate' => $annonce,
                'groups'             => 'annonce:write',
            ]);
        } catch (\Exception) {
            return $this->json(['error' => 'JSON invalide.'], Response::HTTP_BAD_REQUEST);
        }

        $errors = $validator->validate($annonce);
        if (count($errors) > 0) {
            $messages = [];
            foreach ($errors as $error) {
                $messages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $messages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->flush();
        $json = $serializer->serialize($annonce, 'json', ['groups' => 'annonce:read']);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }
}
