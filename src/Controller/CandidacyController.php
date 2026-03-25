<?php

namespace App\Controller;

use App\Entity\Candidacy;
use App\Entity\Offer;
use App\Form\CandidacyType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CandidacyController extends AbstractController
{
    #[Route('/add-candidacy/{id}', name: 'add_candidacy')]
    #[IsGranted("ROLE_USER")]
    public function addCandidacy(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $candidacy = new Candidacy();
        $form = $this->createForm(CandidacyType::class, $candidacy);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $candidacy->setUser($user);

            $offer = $entityManager->getRepository(Offer::class)->find($id);
            $candidacy->setOffer($offer);

            $entityManager->persist($candidacy);
            $entityManager->flush();

            return $this->redirectToRoute('candidacies');
        }

        return $this->render('candidacy/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/candidacies', name: 'candidacies')]
    public function candidacies(Request $request, EntityManagerInterface $entityManager): Response
    {
        $candidacies = $entityManager->getRepository(Candidacy::class)->findAll();

        return $this->render('candidacy/list.html.twig', [
            'candidacies' => $candidacies
        ]);
    }

    #[Route('/edit-candidacy/{id}', name: 'edit_candidacy')]
    #[IsGranted("ROLE_USER")]
    public function editCandidacy(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $candidacy = $entityManager->getRepository(Candidacy::class)->find($id);

        if (!$candidacy) {
            return $this->redirectToRoute('offers');
        }

        $form = $this->createForm(CandidacyType::class, $candidacy);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($candidacy);
            $entityManager->flush();

            return $this->redirectToRoute('candidacies');
        }

        return $this->render('candidacy/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete-candidacy/{id}', name: 'delete_candidacy')]
    #[IsGranted("ROLE_ADMIN")]
    public function deleteCandidacy(Request $request, EntityManagerInterface $entityManager, $id): Response
    {
        $candidacy = $entityManager->getRepository(Candidacy::class)->find($id);

        if (!$candidacy) {
            return $this->redirectToRoute('candidacies');
        }

        $entityManager->remove($candidacy);
        $entityManager->flush();

        return $this->redirectToRoute('candidacies');
    }
}
