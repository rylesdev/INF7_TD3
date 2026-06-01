<?php

namespace App\Controller;

use App\Entity\Semainier;
use App\Entity\Tache;
use App\Form\TacheType;
use App\Repository\ColocationRepository;
use App\Repository\SemainierRepository;
use App\Repository\TacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/taches')]
#[IsGranted('ROLE_USER')]
class TacheController extends AbstractController
{
    #[Route('/{colocationId}', name: 'app_taches', requirements: ['colocationId' => '\d+'])]
    public function index(
        int $colocationId,
        TacheRepository $tacheRepo,
        SemainierRepository $semainierRepo,
        ColocationRepository $colRepo
    ): Response {
        $colocation = $colRepo->find($colocationId);
        if (!$colocation) {
            throw $this->createNotFoundException('Colocation non trouvée.');
        }

        $taches    = $tacheRepo->findByColocation($colocationId);
        $semaine   = $semainierRepo->findSemaineCourante($colocationId);
        $semainier = $this->organiserSemainier($semaine);

        return $this->render('tache/index.html.twig', [
            'taches'     => $taches,
            'semainier'  => $semainier,
            'colocation' => $colocation,
        ]);
    }

    #[Route('/{colocationId}/new', name: 'app_tache_new', methods: ['GET', 'POST'], requirements: ['colocationId' => '\d+'])]
    public function new(
        int $colocationId,
        Request $request,
        EntityManagerInterface $em,
        ColocationRepository $colRepo
    ): Response {
        $colocation = $colRepo->find($colocationId);
        if (!$colocation) throw $this->createNotFoundException();

        $tache = new Tache();
        $tache->setColocation($colocation);
        $form  = $this->createForm(TacheType::class, $tache, ['colocation' => $colocation]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($tache);

            // Création entrée semainier
            if ($tache->getDateEcheance()) {
                $semainier = new Semainier();
                $semainier->setTache($tache);
                $semainier->setJourSemaine((int) $tache->getDateEcheance()->format('N'));
                $semainier->setDateDebut(new \DateTimeImmutable('monday this week'));
                $semainier->setDateFin(new \DateTimeImmutable('sunday this week'));
                $em->persist($semainier);
            }

            $em->flush();
            $this->addFlash('success', 'Tâche créée et ajoutée au semainier.');
            return $this->redirectToRoute('app_taches', ['colocationId' => $colocationId]);
        }

        return $this->render('tache/new.html.twig', ['form' => $form->createView(), 'colocation' => $colocation]);
    }

    #[Route('/done/{id}', name: 'app_tache_done', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function markDone(Tache $tache, EntityManagerInterface $em): Response
    {
        $tache->setStatut(Tache::STATUT_TERMINEE);
        $em->flush();

        // Reconduite automatique de la tâche récurrente la semaine suivante
        if ($tache->getSemainier()) {
            $oldSemainier = $tache->getSemainier();
            $nouvelleTache = new Tache();
            $nouvelleTache->setTitre($tache->getTitre());
            $nouvelleTache->setType($tache->getType());
            $nouvelleTache->setColocation($tache->getColocation());
            $nouvelleTache->setAssigne($tache->getAssigne());

            $debut    = new \DateTimeImmutable('monday next week');
            $fin      = new \DateTimeImmutable('sunday next week');
            $echeance = $debut->modify('+' . ($oldSemainier->getJourSemaine() - 1) . ' days');
            $nouvelleTache->setDateEcheance($echeance);
            $em->persist($nouvelleTache);

            $newSemainier = new Semainier();
            $newSemainier->setTache($nouvelleTache);
            $newSemainier->setJourSemaine($oldSemainier->getJourSemaine());
            $newSemainier->setDateDebut($debut);
            $newSemainier->setDateFin($fin);

            $em->persist($newSemainier);
            $em->flush();
        }

        $this->addFlash('success', 'Tâche terminée. Nouvelle tâche créée pour la semaine prochaine.');
        return $this->redirectToRoute('app_taches', ['colocationId' => $tache->getColocation()->getId()]);
    }

    #[Route('/delete/{id}', name: 'app_tache_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Tache $tache, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_tache_' . $tache->getId(), $request->request->get('_token'))) {
            $colocationId = $tache->getColocation()->getId();
            $em->remove($tache);
            $em->flush();
            $this->addFlash('success', 'Tâche supprimée.');
            return $this->redirectToRoute('app_taches', ['colocationId' => $colocationId]);
        }
        return $this->redirectToRoute('app_taches', ['colocationId' => $tache->getColocation()->getId()]);
    }

    private function organiserSemainier(array $semaine): array
    {
        $jours = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $result = [];
        foreach ($jours as $i => $jour) {
            $result[$jour] = [];
        }
        foreach ($semaine as $s) {
            $j = $s->getLibelleJour();
            $result[$j][] = $s->getTache();
        }
        return $result;
    }
}
