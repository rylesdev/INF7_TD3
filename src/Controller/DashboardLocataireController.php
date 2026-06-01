<?php

namespace App\Controller;

use App\Entity\Annonce;
use App\Entity\Loyer;
use App\Entity\Message;
use App\Entity\Notification;
use App\Entity\Tache;
use App\Form\MessageType;
use App\Entity\EvaluationProprietaire;
use App\Form\EvaluationProprietaireType;
use App\Repository\ColocationRepository;
use App\Repository\EvaluationLocataireRepository;
use App\Repository\EvaluationProprietaireRepository;
use App\Repository\LoyerRepository;
use App\Repository\MessageRepository;
use App\Repository\NotificationRepository;
use App\Repository\QuittanceRepository;
use App\Repository\TacheRepository;
use App\Repository\TantiemeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/locataire')]
#[IsGranted('ROLE_USER')]
class DashboardLocataireController extends AbstractController
{
    #[Route('', name: 'app_locataire_dashboard')]
    public function dashboard(
        LoyerRepository $loyerRepo,
        TacheRepository $tacheRepo,
        NotificationRepository $notifRepo,
        MessageRepository $messageRepo,
        EntityManagerInterface $em
    ): Response {
        $loyerRepo->marquerEnRetard($em);
        $user    = $this->getUser();
        $chambre = $user->getChambres()->first();
        $loyers  = $chambre ? $chambre->getLoyers()->toArray() : [];
        $taches  = $chambre ? $tacheRepo->findByColocation($chambre->getColocation()->getId()) : [];

        $mois = (int) date('n');
        $annee = (int) date('Y');
        $loyerEnRetard = false;
        foreach ($loyers as $l) {
            if ($l->getMois() === $mois && $l->getAnnee() === $annee && $l->getStatut() !== Loyer::STATUT_PAYE) {
                $loyerEnRetard = true;
                break;
            }
        }

        $tachesAFaire = count(array_filter($taches, fn($t) => in_array($t->getStatut(), [Tache::STATUT_A_FAIRE, Tache::STATUT_EN_COURS])));

        $messagesNonLus = $messageRepo->countNonLus($user->getId());

        return $this->render('locataire/dashboard.html.twig', [
            'derniersLoyers'  => array_slice($loyers, 0, 5),
            'taches'          => $taches,
            'notifications'   => $notifRepo->findNonLues($user->getId()),
            'chambre'         => $chambre,
            'loyerEnRetard'   => $loyerEnRetard,
            'tachesAFaire'    => $tachesAFaire,
            'messagesNonLus'  => $messagesNonLus,
        ]);
    }

    #[Route('/loyers', name: 'app_locataire_loyers')]
    public function loyers(): Response
    {
        $user    = $this->getUser();
        $chambre = $user->getChambres()->first();
        $loyers  = $chambre ? $chambre->getLoyers()->toArray() : [];
        usort($loyers, fn($a, $b) => $a->getAnnee() === $b->getAnnee() ? $b->getMois() - $a->getMois() : $b->getAnnee() - $a->getAnnee());

        return $this->render('locataire/loyers.html.twig', ['loyers' => $loyers, 'chambre' => $chambre]);
    }

    #[Route('/quittances', name: 'app_locataire_quittances')]
    public function quittances(QuittanceRepository $quittanceRepo): Response
    {
        $user    = $this->getUser();
        $chambre = $user->getChambres()->first();
        $loyers  = $chambre ? $chambre->getLoyers()->toArray() : [];

        $quittances = [];
        foreach ($loyers as $loyer) {
            if ($loyer->getQuittance()) {
                $quittances[] = $loyer->getQuittance();
            }
        }

        return $this->render('locataire/quittances.html.twig', ['quittances' => $quittances]);
    }

    #[Route('/quittances/{id}/pdf', name: 'app_locataire_quittance_pdf', requirements: ['id' => '\d+'])]
    public function telechargerQuittance(\App\Entity\Quittance $quittance): Response
    {
        $html = $this->renderView('locataire/quittance_pdf.html.twig', ['quittance' => $quittance]);

        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="quittance-' . $quittance->getId() . '.pdf"',
            ]
        );
    }

    #[Route('/tantiemes', name: 'app_locataire_tantiemes')]
    public function tantiemes(): Response
    {
        $user    = $this->getUser();
        $chambre = $user->getChambres()->first();

        return $this->render('locataire/tantiemes.html.twig', [
            'chambre'    => $chambre,
            'tantiemes'  => $chambre ? $chambre->getTantiemes()->toArray() : [],
            'pourcentage' => $chambre ? $chambre->getPourcentageSurface() : 0,
        ]);
    }

    #[Route('/messagerie/annonce/{id}', name: 'app_locataire_messagerie_annonce', requirements: ['id' => '\d+'])]
    public function messagerieAnnonce(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        MessageRepository $messageRepo
    ): Response {
        $annonce = $em->find(Annonce::class, $id);
        if (!$annonce) {
            throw $this->createNotFoundException();
        }

        $user         = $this->getUser();
        $colocation   = $annonce->getColocation();
        $proprietaire = $colocation?->getProprietaire();

        if (!$proprietaire) {
            $this->addFlash('error', 'Impossible de contacter ce propriétaire.');
            return $this->redirectToRoute('app_annonce_show', ['id' => $id]);
        }

        $messages = $messageRepo->findAllBetweenUsers($user->getId(), $proprietaire->getId());
        $messageRepo->marquerLus($proprietaire->getId(), $user->getId(), $colocation->getId());

        $message = new Message();
        $form    = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setExpediteur($user);
            $message->setDestinataire($proprietaire);
            $message->setColocation($colocation);
            $em->persist($message);

            $notif = new Notification();
            $notif->setUser($proprietaire);
            $notif->setType(Notification::TYPE_NOUVEAU_MESSAGE);
            $notif->setTitre('Nouveau message de ' . $user->getNomComplet());
            $notif->setMessage(mb_substr($message->getContenu(), 0, 100));
            $notif->setLien($this->generateUrl('app_proprietaire_messagerie'));
            $em->persist($notif);

            $em->flush();
            return $this->redirectToRoute('app_locataire_messagerie_annonce', ['id' => $id]);
        }

        $dernierContenu = count($messages) > 0 ? $messages[count($messages) - 1]->getContenu() : '';
        $conversations = [[
            'user'           => $proprietaire,
            'dernierMessage' => mb_substr($dernierContenu, 0, 60),
            'nonLus'         => 0,
        ]];

        return $this->render('locataire/messagerie.html.twig', [
            'messages'      => $messages,
            'proprietaire'  => $proprietaire,
            'conversations' => $conversations,
            'form'          => $form->createView(),
        ]);
    }

    #[Route('/messagerie', name: 'app_locataire_messagerie')]
    public function messagerie(
        Request $request,
        MessageRepository $messageRepo,
        EntityManagerInterface $em,
        UserRepository $userRepo
    ): Response {
        $user          = $this->getUser();
        $conversations = $messageRepo->findAllConversationsForUser($user->getId());

        if (empty($conversations)) {
            return $this->render('locataire/messagerie.html.twig', [
                'messages'        => [],
                'proprietaire'    => null,
                'conversations'   => [],
                'activeUserId'    => null,
                'form'            => $this->createForm(MessageType::class)->createView(),
            ]);
        }

        // Conversation active : paramètre ?with=userId, sinon chambre assignée, sinon la plus récente
        $withId = (int) $request->query->get('with', 0);
        $proprietaire = null;
        $colocation   = null;

        if ($withId) {
            foreach ($conversations as $conv) {
                if ($conv['user']->getId() === $withId) {
                    $proprietaire = $conv['user'];
                    $colocation   = $conv['colocation'];
                    break;
                }
            }
        }

        if (!$proprietaire) {
            $chambre = $user->getChambres()->first() ?: null;
            if ($chambre) {
                $colocation   = $chambre->getColocation();
                $proprietaire = $colocation->getProprietaire();
            } else {
                $colocation   = $conversations[0]['colocation'];
                $proprietaire = $conversations[0]['user'];
            }
        }

        $messages = $messageRepo->findAllBetweenUsers($user->getId(), $proprietaire->getId());
        $messageRepo->marquerLus($proprietaire->getId(), $user->getId(), $colocation->getId());

        $message = new Message();
        $form    = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setExpediteur($user);
            $message->setDestinataire($proprietaire);
            $message->setColocation($colocation);
            $em->persist($message);

            $notif = new Notification();
            $notif->setUser($proprietaire);
            $notif->setType(Notification::TYPE_NOUVEAU_MESSAGE);
            $notif->setTitre('Nouveau message de ' . $user->getNomComplet());
            $notif->setMessage(mb_substr($message->getContenu(), 0, 100));
            $notif->setLien($this->generateUrl('app_proprietaire_messagerie'));
            $em->persist($notif);

            $em->flush();
            return $this->redirectToRoute('app_locataire_messagerie', ['with' => $proprietaire->getId()]);
        }

        return $this->render('locataire/messagerie.html.twig', [
            'messages'      => $messages,
            'proprietaire'  => $proprietaire,
            'conversations' => $conversations,
            'activeUserId'  => $proprietaire->getId(),
            'form'          => $form->createView(),
        ]);
    }

    #[Route('/evaluations', name: 'app_locataire_evaluations')]
    public function evaluations(
        EvaluationLocataireRepository $evalLocRepo,
        EvaluationProprietaireRepository $evalProRepo
    ): Response {
        $user        = $this->getUser();
        $chambre     = $user->getChambres()->first() ?: null;
        $evaluations = $evalLocRepo->findByLocataire($user->getId());
        $moyenne     = $evalLocRepo->moyenneNoteLocataire($user->getId());
        $evalDonnees = $evalProRepo->findByLocataire($user->getId());

        return $this->render('locataire/evaluations.html.twig', [
            'evaluations' => $evaluations,
            'moyenne'     => $moyenne,
            'evalDonnees' => $evalDonnees,
            'chambre'     => $chambre,
        ]);
    }

    #[Route('/evaluer-proprietaire/{id}', name: 'app_locataire_evaluer_proprietaire', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function evaluerProprietaire(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepo,
        ColocationRepository $colRepo,
        EvaluationProprietaireRepository $evalRepo,
        LoyerRepository $loyerRepo
    ): Response {
        $user         = $this->getUser();
        $proprietaire = $userRepo->find($id);
        if (!$proprietaire) {
            throw $this->createNotFoundException();
        }

        // Cherche une chambre (passée ou actuelle) dans une colocation de ce propriétaire
        $chambre = null;
        foreach ($user->getChambres() as $c) {
            if ($c->getColocation()?->getProprietaire()?->getId() === $id) {
                $chambre = $c;
                break;
            }
        }
        $colocation = $chambre?->getColocation();

        if (!$colocation) {
            $this->addFlash('error', 'Vous pouvez évaluer uniquement un propriétaire chez qui vous avez séjourné.');
            return $this->redirectToRoute('app_locataire_evaluations');
        }

        // Vérifie qu'au moins un loyer a été payé dans l'une de ses colocations
        $aPaye = false;
        foreach ($user->getChambres() as $c) {
            if ($loyerRepo->findOneBy(['chambre' => $c, 'statut' => Loyer::STATUT_PAYE])) {
                $aPaye = true;
                break;
            }
        }
        if (!$aPaye) {
            $this->addFlash('error', 'Vous devez avoir payé au moins un loyer pour évaluer ce propriétaire.');
            return $this->redirectToRoute('app_locataire_evaluations');
        }

        $evaluation = $evalRepo->findOneByParties($user->getId(), $id, $colocation->getId())
            ?? new EvaluationProprietaire();
        $isNew = $evaluation->getId() === null;

        $form = $this->createForm(EvaluationProprietaireType::class, $evaluation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($isNew) {
                $evaluation->setLocataire($user);
                $evaluation->setProprietaire($proprietaire);
                $evaluation->setColocation($colocation);
                $em->persist($evaluation);
                $notif = new Notification();
                $notif->setUser($proprietaire);
                $notif->setType(Notification::TYPE_INFO);
                $notif->setTitre($user->getNomComplet() . ' vous a évalué(e)');
                $notif->setMessage('Vous avez reçu un avis de ' . $user->getNomComplet() . '.');
                $notif->setLien($this->generateUrl('app_profil_proprietaire', ['id' => $proprietaire->getId()]));
                $em->persist($notif);
            }
            $em->flush();
            $this->addFlash('success', 'Évaluation enregistrée.');
            return $this->redirectToRoute('app_locataire_evaluations');
        }

        return $this->render('locataire/evaluation_proprietaire_form.html.twig', [
            'form'         => $form->createView(),
            'proprietaire' => $proprietaire,
            'colocation'   => $colocation,
        ]);
    }

    #[Route('/resilier', name: 'app_locataire_resilier', methods: ['GET', 'POST'])]
    public function resilier(Request $request, EntityManagerInterface $em): Response
    {
        $user    = $this->getUser();
        $chambre = $user->getChambres()->first() ?: null;

        if (!$chambre) {
            $this->addFlash('error', 'Vous n\'êtes assigné à aucune chambre.');
            return $this->redirectToRoute('app_locataire_dashboard');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('resilier', $request->request->get('_token'))) {
                throw $this->createAccessDeniedException();
            }

            $colocation = $chambre->getColocation();
            $proprio    = $colocation?->getProprietaire();

            $chambre->setLocataire(null);

            foreach ($colocation->getAnnonces() as $annonce) {
                $annonce->setStatut(\App\Entity\Annonce::STATUT_DISPONIBLE);
            }

            if ($proprio && $colocation) {
                $msgAuto = new Message();
                $msgAuto->setExpediteur($user);
                $msgAuto->setDestinataire($proprio);
                $msgAuto->setColocation($colocation);
                $msgAuto->setContenu($user->getNomComplet() . ' a résilié son bail pour la chambre "' . $chambre->getNom() . '".');
                $msgAuto->setAutomatique(true);
                $em->persist($msgAuto);

                $notif = new Notification();
                $notif->setUser($proprio);
                $notif->setType(Notification::TYPE_INFO);
                $notif->setTitre($user->getNomComplet() . ' a résilié son bail');
                $notif->setMessage('La chambre « ' . $chambre->getNom() . ' » de ' . $colocation->getNom() . ' est à nouveau disponible.');
                $notif->setLien($this->generateUrl('app_proprietaire_loyers'));
                $em->persist($notif);
            }

            $em->flush();
            $this->addFlash('success', 'Votre bail a été résilié. Nous espérons vous revoir bientôt !');
            return $this->redirectToRoute('app_locataire_dashboard');
        }

        return $this->render('locataire/resilier.html.twig', ['chambre' => $chambre]);
    }

    #[Route('/loyers/{id}/payer', name: 'app_locataire_loyer_payer', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function payerLoyer(Loyer $loyer, Request $request, EntityManagerInterface $em): Response
    {
        $user    = $this->getUser();
        $chambre = $user->getChambres()->first();

        if (!$chambre || $loyer->getChambre()?->getId() !== $chambre->getId()) {
            throw $this->createAccessDeniedException();
        }
        if (!$this->isCsrfTokenValid('payer_loyer_' . $loyer->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        if ($loyer->getStatut() !== Loyer::STATUT_PAYE) {
            $loyer->setStatut(Loyer::STATUT_PAYE);
            $loyer->setDatePaiement(new \DateTimeImmutable());

            $this->genererQuittance($loyer, $em);

            $proprio     = $loyer->getColocation()?->getProprietaire();
            $colocation  = $loyer->getColocation();
            if ($proprio && $colocation) {
                $msgAuto = new Message();
                $msgAuto->setExpediteur($user);
                $msgAuto->setDestinataire($proprio);
                $msgAuto->setColocation($colocation);
                $msgAuto->setContenu($user->getNomComplet() . ' a payé le loyer ' . $loyer->getMois() . '/' . $loyer->getAnnee() . ' (' . $loyer->getMontant() . ' €).');
                $msgAuto->setAutomatique(true);
                $msgAuto->setLien($this->generateUrl('app_proprietaire_loyers'));
                $em->persist($msgAuto);

                $notif = new Notification();
                $notif->setUser($proprio);
                $notif->setType(Notification::TYPE_INFO);
                $notif->setTitre('Paiement reçu de ' . $user->getNomComplet());
                $notif->setMessage($user->getNomComplet() . ' a payé le loyer ' . $loyer->getMois() . '/' . $loyer->getAnnee() . ' (' . $loyer->getMontant() . ' €).');
                $notif->setLien($this->generateUrl('app_proprietaire_loyers'));
                $em->persist($notif);
            }

            $em->flush();
            $this->addFlash('success', 'Paiement simulé avec succès. Votre propriétaire a été notifié.');
        }

        return $this->redirectToRoute('app_locataire_loyers');
    }

    private function genererQuittance(\App\Entity\Loyer $loyer, EntityManagerInterface $em): void
    {
        if ($loyer->getQuittance()) {
            return;
        }
        $debut = \DateTimeImmutable::createFromFormat('Y-m-d', $loyer->getAnnee() . '-' . str_pad((string)$loyer->getMois(), 2, '0', STR_PAD_LEFT) . '-01');
        $fin   = $debut->modify('last day of this month');

        $quittance = new \App\Entity\Quittance();
        $quittance->setLoyer($loyer);
        $quittance->setMontantLoyer($loyer->getMontant());
        $quittance->setMontantCharges('0.00');
        $quittance->setMontantTotal($loyer->getMontant());
        $quittance->setPeriodeDebut($debut);
        $quittance->setPeriodeFin($fin);
        $em->persist($quittance);
    }

    #[Route('/notifications', name: 'app_locataire_notifications')]
    public function notifications(NotificationRepository $repo, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        // Récupérer toutes les notifications et marquer les non-lues
        $notifications = $repo->findAllByUser($user->getId());
        foreach ($notifications as $n) {
            if (!$n->isLue()) {
                $n->setLue(true);
            }
        }
        $em->flush();

        return $this->render('locataire/notifications.html.twig', ['notifications' => $notifications]);
    }
}
