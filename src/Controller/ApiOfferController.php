<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Offer;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;


#[Route('/api/offers', name: 'api_offers_')]
final class ApiOfferController extends AbstractController
{
    #[Route('/', name: 'list', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $offers = $entityManager->getRepository(Offer::class)->findAll();
        //Ancien code//
        // $data= array_map(fn($offers) => [
        //     'id' => $offers->getId(),
        //     'tittle' => $offers->getTittle(),
        //     'description' => $offers->getDescription(),
        //     'createdAt' => $offers->getCreatedAt()->format('Y-m-d H:i:s'),
        // ], $offers);

        //avec Serializer//
        $data = $serializer->serialize($offers, 'json', ['groups' => 'offer:read']);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    #[Route('/add_offer', name: 'add_offer', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Put]
    #[OA\RequestBody(
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
            ]
        )
    )]
    public function addOffer(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $offer = $serializer->deserialize($request->getContent(), Offer::class, 'json');

        $user = $this->getUser();
        $offer->setUser($user);

        $entityManager->persist($offer);
        $entityManager->flush();

        $data = $serializer->serialize($offer, 'json', ['groups' => 'offer:read']);

        return new JsonResponse($data, Response::HTTP_CREATED, [], true);
    }


    #[Route('/editOffer/{id}', name: 'editOffer')]
    #[IsGranted('ROLE_ADMIN')]
    public function editOffer(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer, $id): JsonResponse
    {
        $offer = $entityManager->getRepository(Offer::class)->find($id);

        if (!$offer) {
            return new JsonResponse(['message' => 'Offer not found'], Response::HTTP_NOT_FOUND);
        }

        $updateOffer = $serializer->deserialize($request->getContent(), Offer::class, 'json', ['object_to_populate' => $offer]);

        $entityManager->persist($offer);
        $entityManager->flush();

        return new JsonResponse($updateOffer, Response::HTTP_OK, [], true);
    }
    
    #[Route('/deleteOffer/{id}', name: 'deleteOffer')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteOffer(Request $request, EntityManagerInterface $entityManager, $id): JsonResponse
    {
        $offer = $entityManager->getRepository(Offer::class)->find($id);

        if (!$offer) {
            return new JsonResponse(['message' => 'Offer not found'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($offer);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Offer deleted successfully'], Response::HTTP_NO_CONTENT);
    }
}
