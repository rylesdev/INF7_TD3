<?php

namespace App\Controller;

use App\Entity\Charge;
use App\Entity\Colocation;
use App\Entity\Loyer;
use App\Entity\Message;
use App\Entity\Notification;
use App\Entity\Quittance;
use App\Entity\Tantieme;
use App\Form\ChargeType;
use App\Form\ColocationType;
use App\Form\LoyerType;
use App\Form\MessageType;
use App\Repository\AnnonceRepository;
use App\Repository\ChargeRepository;
use App\Repository\ColocationRepository;
use App\Repository\LoyerRepository;
use App\Repository\MessageRepository;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/proprietaire')]
#[IsGranted('ROLE_PROPRIETAIRE')]
class DashboardProprietaireController extends AbstractController
{
    #[Route('', name: 'app_proprietaire_dashboard')]
    public function dashboard(
        ColocationRepository $colocationRepo,
        LoyerRepository $loyerRepo,
        ChargeRepository $chargeRepo,
        NotificationRepository $notifRepo
    ): Response {
        $user       = $this->getUser();
        $colocations = $colocationRepo->findByProprietaire($user->getId());
        $annee      = (int) date('Y');

        $statsLoyers  = ['payes' => 0, 'impayes' => 0, 'total' => 0];
        $chargesParMois = [];

        foreach ($colocations as $col) {
            $loyers = $loyerRepo->findByColocation($col->getId());
            foreach ($loyers as $l) {
                $statsLoyers['total']++;
                if ($l->isPaye()) {
                    $statsLoyers['payes']++;
                } else {
                    $statsLoyers['impayes']++;
                }
            }
            $charges = $chargeRepo->findByColocationAndAnnee($col->getId(), $annee);
            foreach ($charges as $c) {
                $mois = $c->getMois() ?? date('m', $c->getDate()->getTimestamp());
                $chargesParMois[$mois] = ($chargesParMois[$mois] ?? 0) + (float) $c->getMontant();
            }
        }

        $nbAnnonces = 0;
        foreach ($colocations as $col) {
            $nbAnnonces += $col->getAnnonces()->count();
        }

        $alertes = [];
        if ($statsLoyers['impayes'] > 0) {
            $alertes[] = ['type' => 'danger', 'message' => $statsLoyers['impayes'] . ' loyer(s) impayé(s) ce mois.'];
        }

        return $this->render('proprietaire/dashboard.html.twig', [
            'stats' => [
                'nbColocations' => count($colocations),
                'loyersPercus'  => $statsLoyers['payes'],
                'loyersImpayes' => $statsLoyers['impayes'],
                'nbAnnonces'    => $nbAnnonces,
            ],
            'alertes'        => $alertes,
            'colocations'    => $colocations,
            'notifications'  => $notifRepo->findNonLues($user->getId()),
        ]);
    }

    #[Route('/colocations', name: 'app_proprietaire_colocations')]
    public function colocations(ColocationRepository $repo): Response
    {
        return $this->render('proprietaire/colocations.html.twig', [
            'colocations' => $repo->findByProprietaire($this->getUser()->getId()),
        ]);
    }

    #[Route('/colocations/new', name: 'app_proprietaire_colocation_new', methods: ['GET', 'POST'])]
    public function newColocation(Request $request, EntityManagerInterface $em): Response
    {
        $col  = new Colocation();
        $form = $this->createForm(ColocationType::class, $col);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $col->setProprietaire($this->getUser());
            $em->persist($col);
            $em->flush();
            $this->addFlash('success', 'Colocation créée.');
            return $this->redirectToRoute('app_proprietaire_colocations');
        }

        return $this->render('proprietaire/colocation_form.html.twig', ['form' => $form->createView(), 'colocation' => null]);
    }

    #[Route('/colocations/{id}/edit', name: 'app_proprietaire_colocation_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function editColocation(Colocation $col, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_PROPRIETAIRE');
        $form = $this->createForm(ColocationType::class, $col);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Colocation mise à jour.');
            return $this->redirectToRoute('app_proprietaire_colocations');
        }

        return $this->render('proprietaire/colocation_form.html.twig', ['form' => $form->createView(), 'colocation' => $col]);
    }

    #[Route('/loyers', name: 'app_proprietaire_loyers')]
    public function loyers(ColocationRepository $colRepo, LoyerRepository $loyerRepo): Response
    {
        $colocations = $colRepo->findByProprietaire($this->getUser()->getId());
        $loyers = [];
        foreach ($colocations as $col) {
            foreach ($loyerRepo->findByColocation($col->getId()) as $l) {
                $loyers[] = $l;
            }
        }

        return $this->render('proprietaire/loyers.html.twig', ['loyers' => $loyers]);
    }

    #[Route('/loyers/new', name: 'app_proprietaire_loyer_new', methods: ['GET', 'POST'])]
    public function newLoyer(Request $request, EntityManagerInterface $em, ColocationRepository $colRepo): Response
    {
        $loyer = new Loyer();
        $form  = $this->createForm(LoyerType::class, $loyer, [
            'colocations' => $colRepo->findByProprietaire($this->getUser()->getId()),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($loyer);
            $em->flush();

            if ($loyer->isPaye()) {
                $this->genererQuittance($loyer, $em);
            }

            $this->addFlash('success', 'Loyer enregistré.');
            return $this->redirectToRoute('app_proprietaire_loyers');
        }

        return $this->render('proprietaire/loyer_form.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/loyers/{id}/payer', name: 'app_proprietaire_loyer_payer', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function payerLoyer(Loyer $loyer, EntityManagerInterface $em): Response
    {
        $loyer->setStatut(Loyer::STATUT_PAYE);
        $loyer->setDatePaiement(new \DateTime());
        $em->flush();

        if (!$loyer->getQuittance()) {
            $this->genererQuittance($loyer, $em);
        }

        $this->addFlash('success', 'Loyer marqué comme payé. Quittance générée.');
        return $this->redirectToRoute('app_proprietaire_loyers');
    }

    private function genererQuittance(Loyer $loyer, EntityManagerInterface $em): void
    {
        $debut = \DateTime::createFromFormat('Y-m-d', $loyer->getAnnee() . '-' . $loyer->getMois() . '-01');
        $fin   = (clone $debut)->modify('last day of this month');

        $quittance = new Quittance();
        $quittance->setLoyer($loyer);
        $quittance->setMontantLoyer($loyer->getMontant());
        $quittance->setMontantCharges('0.00');
        $quittance->setMontantTotal($loyer->getMontant());
        $quittance->setPeriodeDebut($debut);
        $quittance->setPeriodeFin($fin);
        $em->persist($quittance);
        $em->flush();
    }

    #[Route('/charges', name: 'app_proprietaire_charges')]
    public function charges(ColocationRepository $colRepo, ChargeRepository $chargeRepo): Response
    {
        $annee   = (int) date('Y');
        $charges = [];
        foreach ($colRepo->findByProprietaire($this->getUser()->getId()) as $col) {
            foreach ($chargeRepo->findByColocationAndAnnee($col->getId(), $annee) as $c) {
                $charges[] = $c;
            }
        }

        return $this->render('proprietaire/charges.html.twig', ['charges' => $charges, 'annee' => $annee]);
    }

    #[Route('/charges/new', name: 'app_proprietaire_charge_new', methods: ['GET', 'POST'])]
    public function newCharge(Request $request, EntityManagerInterface $em, ColocationRepository $colRepo): Response
    {
        $charge = new Charge();
        $form   = $this->createForm(ChargeType::class, $charge, [
            'colocations' => $colRepo->findByProprietaire($this->getUser()->getId()),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $charge->setAnnee((int) $charge->getDate()->format('Y'));
            $charge->setMois($charge->getDate()->format('m'));
            $em->persist($charge);

            // Calcul automatique des tantièmes
            $this->calculerTantiemes($charge, $em);

            $em->flush();
            $this->addFlash('success', 'Charge enregistrée et tantièmes calculés.');
            return $this->redirectToRoute('app_proprietaire_charges');
        }

        return $this->render('proprietaire/charge_form.html.twig', ['form' => $form->createView()]);
    }

    private function calculerTantiemes(Charge $charge, EntityManagerInterface $em): void
    {
        $colocation    = $charge->getColocation();
        $surfaceTotale = $colocation->getSurfaceTotale();

        if ($surfaceTotale <= 0) return;

        foreach ($colocation->getChambres() as $chambre) {
            $pct     = ((float) $chambre->getSurface() / $surfaceTotale) * 100;
            $montant = ((float) $charge->getMontant() * $pct) / 100;

            $tantieme = new Tantieme();
            $tantieme->setChambre($chambre);
            $tantieme->setCharge($charge);
            $tantieme->setPourcentage(round($pct, 2));
            $tantieme->setMontantDu(round($montant, 2));
            $em->persist($tantieme);
        }
    }

    #[Route('/annonces', name: 'app_proprietaire_annonces')]
    public function annonces(AnnonceRepository $annonceRepo): Response
    {
        return $this->render('proprietaire/annonces.html.twig', [
            'annonces' => $annonceRepo->findByProprietaire($this->getUser()->getId()),
        ]);
    }

    #[Route('/messagerie', name: 'app_proprietaire_messagerie')]
    public function messagerie(ColocationRepository $colRepo, MessageRepository $messageRepo): Response
    {
        $colocations   = $colRepo->findByProprietaire($this->getUser()->getId());
        $conversations = [];

        foreach ($colocations as $col) {
            foreach ($col->getChambres() as $chambre) {
                if (!$chambre->getLocataire()) {
                    continue;
                }
                $loc      = $chambre->getLocataire();
                $messages = $messageRepo->findConversation($this->getUser()->getId(), $loc->getId(), $col->getId());
                $dernier  = count($messages) > 0 ? $messages[array_key_last($messages)]->getContenu() : '';
                $conversations[] = [
                    'user'         => $loc,
                    'colocation'   => $col,
                    'dernierMessage' => mb_substr($dernier, 0, 60),
                    'nonLus'       => 0,
                ];
            }
        }

        return $this->render('proprietaire/messagerie.html.twig', ['conversations' => $conversations]);
    }

    #[Route('/messagerie/{colocationId}/{locataireId}', name: 'app_proprietaire_conversation', requirements: ['colocationId' => '\d+', 'locataireId' => '\d+'])]
    public function conversation(
        int $colocationId,
        int $locataireId,
        Request $request,
        EntityManagerInterface $em,
        MessageRepository $messageRepo,
        \App\Repository\UserRepository $userRepo,
        ColocationRepository $colRepo
    ): Response {
        $user        = $this->getUser();
        $locataire   = $userRepo->find($locataireId);
        $colocation  = $colRepo->find($colocationId);
        $messages    = $messageRepo->findConversation($user->getId(), $locataireId, $colocationId);

        $message = new Message();
        $form    = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setExpediteur($user);
            $message->setDestinataire($locataire);
            $message->setColocation($colocation);
            $em->persist($message);

            // Notification pour le locataire
            $notif = new Notification();
            $notif->setUser($locataire);
            $notif->setType(Notification::TYPE_NOUVEAU_MESSAGE);
            $notif->setTitre('Nouveau message de ' . $user->getNomComplet());
            $em->persist($notif);

            $em->flush();
            return $this->redirectToRoute('app_proprietaire_conversation', ['colocationId' => $colocationId, 'locataireId' => $locataireId]);
        }

        return $this->render('proprietaire/conversation.html.twig', [
            'messages'      => $messages,
            'interlocuteur' => $locataire,
            'colocation'    => $colocation,
            'form'          => $form->createView(),
        ]);
    }

    #[Route('/api/stats', name: 'app_proprietaire_stats_api')]
    public function statsApi(ColocationRepository $colRepo, ChargeRepository $chargeRepo, LoyerRepository $loyerRepo): JsonResponse
    {
        $annee   = (int) date('Y');
        $moisLabels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
        $chargesData = array_fill(0, 12, 0);
        $loyersData  = ['payes' => 0, 'impayes' => 0];

        foreach ($colRepo->findByProprietaire($this->getUser()->getId()) as $col) {
            foreach ($chargeRepo->findByColocationAndAnnee($col->getId(), $annee) as $c) {
                $idx = (int) $c->getDate()->format('n') - 1;
                $chargesData[$idx] += (float) $c->getMontant();
            }
            foreach ($loyerRepo->findByColocation($col->getId()) as $l) {
                $l->isPaye() ? $loyersData['payes']++ : $loyersData['impayes']++;
            }
        }

        return $this->json(['labels' => $moisLabels, 'revenus' => $chargesData, 'charges' => $chargesData, 'loyers' => $loyersData]);
    }

    #[Route('/colocations/{id}/delete', name: 'app_proprietaire_colocation_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deleteColocation(Colocation $col, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_colocation_' . $col->getId(), $request->request->get('_token'))) {
            $em->remove($col);
            $em->flush();
            $this->addFlash('success', 'Colocation supprimée.');
        }
        return $this->redirectToRoute('app_proprietaire_colocations');
    }

    #[Route('/loyers/{id}/edit', name: 'app_proprietaire_loyer_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function editLoyer(Loyer $loyer, Request $request, EntityManagerInterface $em, ColocationRepository $colRepo): Response
    {
        $form = $this->createForm(LoyerType::class, $loyer, [
            'colocations' => $colRepo->findByProprietaire($this->getUser()->getId()),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Loyer mis à jour.');
            return $this->redirectToRoute('app_proprietaire_loyers');
        }

        return $this->render('proprietaire/loyer_form.html.twig', ['form' => $form->createView(), 'loyer' => $loyer]);
    }

    #[Route('/loyers/{id}/delete', name: 'app_proprietaire_loyer_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deleteLoyer(Loyer $loyer, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_loyer_' . $loyer->getId(), $request->request->get('_token'))) {
            $em->remove($loyer);
            $em->flush();
            $this->addFlash('success', 'Loyer supprimé.');
        }
        return $this->redirectToRoute('app_proprietaire_loyers');
    }

    #[Route('/loyers/{id}/quittance', name: 'app_proprietaire_generer_quittance', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function genererQuittanceRoute(Loyer $loyer, EntityManagerInterface $em): Response
    {
        if (!$loyer->getQuittance()) {
            $this->genererQuittance($loyer, $em);
            $this->addFlash('success', 'Quittance générée.');
        }
        return $this->redirectToRoute('app_proprietaire_loyers');
    }

    #[Route('/charges/{id}/tantiemes', name: 'app_proprietaire_calculer_tantiemes', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function recalculerTantiemes(Charge $charge, EntityManagerInterface $em): Response
    {
        $this->calculerTantiemes($charge, $em);
        $em->flush();
        $this->addFlash('success', 'Tantièmes calculés.');
        return $this->redirectToRoute('app_proprietaire_charges');
    }

    #[Route('/charges/{id}/edit', name: 'app_proprietaire_charge_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function editCharge(Charge $charge, Request $request, EntityManagerInterface $em, ColocationRepository $colRepo): Response
    {
        $form = $this->createForm(ChargeType::class, $charge, [
            'colocations' => $colRepo->findByProprietaire($this->getUser()->getId()),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Charge mise à jour.');
            return $this->redirectToRoute('app_proprietaire_charges');
        }

        return $this->render('proprietaire/charge_form.html.twig', ['form' => $form->createView(), 'charge' => $charge]);
    }

    #[Route('/charges/{id}/delete', name: 'app_proprietaire_charge_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function deleteCharge(Charge $charge, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_charge_' . $charge->getId(), $request->request->get('_token'))) {
            $em->remove($charge);
            $em->flush();
            $this->addFlash('success', 'Charge supprimée.');
        }
        return $this->redirectToRoute('app_proprietaire_charges');
    }
}
