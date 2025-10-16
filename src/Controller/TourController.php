<?php

namespace App\Controller;

use App\Entity\Tour;
use App\Form\TourType;
use App\Repository\TourRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tour')]
final class TourController extends AbstractController
{
    #[Route(name: 'app_tour_index', methods: ['GET'])]
    public function index(Request $request, TourRepository $tourRepository): Response
    {
        // read search term from query string (?q=...)
        $q = trim((string) $request->query->get('q', ''));

        // base query (include exhibition for the table)
        $qb = $tourRepository->createQueryBuilder('t')
        ->leftJoin('t.exhibition', 'e')
        ->addSelect('e')
        ->orderBy('t.id', 'ASC'); // ⬅ lowest → highest by id

        // apply filters when searching (cross-field, case-insensitive)
        if ($q !== '') {
            $qb->andWhere('
                LOWER(COALESCE(t.name, \'\'))      LIKE :q
                OR LOWER(COALESCE(t.email, \'\'))   LIKE :q
                OR LOWER(COALESCE(t.phoneNumber, \'\')) LIKE :q
                OR LOWER(COALESCE(t.status, \'\'))  LIKE :q
                OR LOWER(COALESCE(t.notes, \'\'))   LIKE :q
                OR LOWER(COALESCE(e.title, \'\'))   LIKE :q
                OR LOWER(COALESCE(e.type, \'\'))    LIKE :q
                OR LOWER(COALESCE(e.period, \'\'))  LIKE :q
            ')
            ->setParameter('q', '%'.mb_strtolower($q).'%');

            // If q looks like YYYY-MM-DD, also match tours on that date
            $asDate = \DateTimeImmutable::createFromFormat('Y-m-d', $q);
            if ($asDate instanceof \DateTimeImmutable) {
                $qb->orWhere('t.date BETWEEN :startDate AND :endDate')
                   ->setParameter('startDate', $asDate->setTime(0, 0, 0))
                   ->setParameter('endDate',   $asDate->setTime(23, 59, 59));
            }
        }

        $tours = $qb->getQuery()->getResult();

        return $this->render('tour/index.html.twig', [
            'tours' => $tours,
            'q'     => $q, // pass current search back to the view
        ]);
    }

    #[Route('/new', name: 'app_tour_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tour = new Tour();
        $form = $this->createForm(TourType::class, $tour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tour);
            $entityManager->flush();

            // add a flash for the UI
            $this->addFlash('success', 'Tour created successfully.');

            return $this->redirectToRoute('app_tour_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tour/new.html.twig', [
            'tour' => $tour,
            // pass FormView (NOT the Form object)
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_tour_show', methods: ['GET'])]
    public function show(Tour $tour): Response
    {
        return $this->render('tour/show.html.twig', [
            'tour' => $tour,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_tour_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tour $tour, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TourType::class, $tour);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // success flash
            $this->addFlash('success', 'Tour updated successfully.');

            return $this->redirectToRoute('app_tour_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tour/edit.html.twig', [
            'tour' => $tour,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_tour_delete', methods: ['POST'])]
    public function delete(Request $request, Tour $tour, EntityManagerInterface $entityManager): Response
    {
        // retrieve the token from the POST payload
        $token = $request->request->get('_token');

        // validate the token; keep the same token id you used in the form (here 'delete'.$id)
        if ($this->isCsrfTokenValid('delete'.$tour->getId(), $token)) {
            $entityManager->remove($tour);
            $entityManager->flush();

            $this->addFlash('success', 'Tour deleted.');
        } else {
            $this->addFlash('error', 'Invalid CSRF token. Delete failed.');
        }

        return $this->redirectToRoute('app_tour_index', [], Response::HTTP_SEE_OTHER);
    }
}
