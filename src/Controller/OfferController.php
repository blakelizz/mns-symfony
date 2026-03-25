<?php

namespace App\Controller;

use App\Entity\Offer;
use APP\Entity\User;
use App\Form\OfferType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

final class OfferController extends AbstractController
{
    #[Route('/add_offer', name: 'add_offer')]
    #[IsGranted('ROLE_ADMIN')]
    public function addOffer(Request $request, EntityManagerInterface $entityManager): Response
    {
        $offer = new Offer();
        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $offer->setUser($user);

            $entityManager->persist($offer);
            $entityManager->flush();

            return $this->redirectToRoute(route: 'offers');
        }

        return $this->render(view: 'offer/add.html.twig', parameters: ['form' => $form]);
    }

    #[Route('/offers', name: 'offers')]
    public function offers(Request $request, EntityManagerInterface $entityManager): Response
    {
        $offers = $entityManager->getRepository(Offer::class)->findAll();

        $user = $this->getUser();
        //$user->setRole(['ROLE_ADMIN']);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->render(view: 'offer/list.html.twig', parameters: ['offers' => $offers]);
    }

    #[Route('/editOffer/{id}', name: 'editOffer')]
    #[IsGranted('ROLE_ADMIN')]
    public function editOffer(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $offer = $entityManager->getRepository(Offer::class)->find($id);

        if (!$offer) {
            return $this->redirectToRoute(route: 'offers');
        }

        $form = $this->createForm(OfferType::class, $offer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            $offer->setUser($user);

            $entityManager->persist($offer);
            $entityManager->flush();
            return $this->redirectToRoute(route: 'offers');
        }

        return $this->render(view: 'offer/edit.html.twig', parameters: ['form' => $form]);
    }

    #[Route('/deleteOffer/{id}', name: 'deleteOffer')]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteOffer(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $offer = $entityManager->getRepository(Offer::class)->find($id);

        if (!$offer) {
            return $this->redirectToRoute(route: 'offers');
        }

        $entityManager->remove($offer);
        $entityManager->flush();

        return $this->redirectToRoute(route: 'offers');
    }
}
