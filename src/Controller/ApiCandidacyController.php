<?php

namespace App\Controller;

use App\Entity\Candidacy;
use App\Entity\Offer;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Attributes as OA;

#[Route('/api/candidacies', name: 'api_candidacies_')]
final class ApiCandidacyController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns the candidacies',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Candidacy::class, groups: ['candidacy:read']))
        )
    )]
    public function index(EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $candidacies = $entityManager->getRepository(Candidacy::class)->findAll();
        $data = $serializer->serialize($candidacies, 'json', ['groups' => 'candidacy:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/add-candidacy/{id}', name: 'add', methods: ['POST'])]
    #[OA\Response(
        response: 201,
        description: 'Return the created candidacy',
        content: new OA\JsonContent(
            ref: new Model(type: Candidacy::class, groups: ['candidacy:read'])
        )
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'file', type: 'string'),
            ]
        )
    )]
    public function addCandidacy(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, $id): JsonResponse
    {
        $candidacy = $serializer->deserialize($request->getContent(), Candidacy::class, 'json');
        $candidacy->setUser($this->getUser());

        $offer = $entityManager->getRepository(Offer::class)->find($id);
        $candidacy->setOffer($offer);

        $entityManager->persist($candidacy);
        $entityManager->flush();

        $data = $serializer->serialize($candidacy, 'json', ['groups' => 'candidacy:read']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }

    #[Route('/edit-candidacy/{id}', name: 'edit', methods: ['PUT'])]
    #[OA\Response(
        response: 200,
        description: 'Return the updated candidacy',
        content: new OA\JsonContent(
            ref: new Model(type: Candidacy::class, groups: ['candidacy:read'])
        )
    )]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'file', type: 'string'),
            ]
        )
    )]
    public function editCandidacy(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, $id): JsonResponse
    {
        $candidacy = $entityManager->getRepository(Candidacy::class)->find($id);

        if (!$candidacy) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $updatedCandidacy = $serializer->deserialize($request->getContent(), Candidacy::class, 'json', ['object_to_populate' => $candidacy]);

        $entityManager->persist($updatedCandidacy);
        $entityManager->flush();

        $data = $serializer->serialize($candidacy, 'json', ['groups' => 'candidacy:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/delete-candidacy/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Response(
        response: 204,
        description: 'Candidacy deleted successfully',
    )]
    public function deleteCandidacy(EntityManagerInterface $entityManager, $id): JsonResponse
    {
        $candidacy = $entityManager->getRepository(Candidacy::class)->find($id);
        if (!$candidacy) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }
        $entityManager->remove($candidacy);
        $entityManager->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
