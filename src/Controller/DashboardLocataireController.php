<?php

namespace App\Controller;

use App\Entity\Loyer;
use App\Entity\Message;
use App\Entity\Tache;
use App\Form\MessageType;
use App\Repository\EvaluationLocataireRepository;
use App\Repository\LoyerRepository;
use App\Repository\MessageRepository;
use App\Repository\NotificationRepository;
use App\Repository\QuittanceRepository;
use App\Repository\TacheRepository;
use App\Repository\TantiemeRepository;
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
        MessageRepository $messageRepo
    ): Response {
        $user    = $this->getUser();
        $chambre = $user->getChambres()->first();
        $loyers  = $chambre ? $loyerRepo->findByColocation($chambre->getColocation()->getId()) : [];
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
    public function loyers(LoyerRepository $loyerRepo): Response
    {
        $user    = $this->getUser();
        $chambre = $user->getChambres()->first();
        $loyers  = $chambre ? $loyerRepo->findByColocation($chambre->getColocation()->getId()) : [];

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

    #[Route('/messagerie', name: 'app_locataire_messagerie')]
    public function messagerie(
        Request $request,
        MessageRepository $messageRepo,
        EntityManagerInterface $em
    ): Response {
        $user    = $this->getUser();
        $chambre = $user->getChambres()->first();
        if (!$chambre) {
            $this->addFlash('warning', 'Vous n\'êtes assigné à aucune chambre.');
            return $this->redirectToRoute('app_locataire_dashboard');
        }

        $colocation    = $chambre->getColocation();
        $proprietaire  = $colocation->getProprietaire();
        $messages      = $messageRepo->findConversation($user->getId(), $proprietaire->getId(), $colocation->getId());

        // Marquer les messages reçus comme lus
        $messageRepo->marquerLus($proprietaire->getId(), $user->getId(), $colocation->getId());

        $message = new Message();
        $form    = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setExpediteur($user);
            $message->setDestinataire($proprietaire);
            $message->setColocation($colocation);
            $em->persist($message);
            $em->flush();

            return $this->redirectToRoute('app_locataire_messagerie');
        }

        $dernierContenu = count($messages) > 0 ? $messages[count($messages) - 1]->getContenu() : '';
        $conversations = [[
            'user'          => $proprietaire,
            'dernierMessage' => mb_substr($dernierContenu, 0, 60),
            'nonLus'        => 0,
        ]];

        return $this->render('locataire/messagerie.html.twig', [
            'messages'      => $messages,
            'proprietaire'  => $proprietaire,
            'conversations' => $conversations,
            'form'          => $form->createView(),
        ]);
    }

    #[Route('/evaluations', name: 'app_locataire_evaluations')]
    public function evaluations(EvaluationLocataireRepository $evalRepo): Response
    {
        $evaluations = $evalRepo->findByLocataire($this->getUser()->getId());
        $moyenne     = $evalRepo->moyenneNoteLocataire($this->getUser()->getId());

        return $this->render('locataire/evaluations.html.twig', [
            'evaluations' => $evaluations,
            'moyenne'     => $moyenne,
        ]);
    }

    #[Route('/notifications', name: 'app_locataire_notifications')]
    public function notifications(NotificationRepository $repo, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $notifications = $repo->findNonLues($user->getId());

        foreach ($notifications as $n) {
            $n->setLue(true);
        }
        $em->flush();

        return $this->render('locataire/notifications.html.twig', ['notifications' => $notifications]);
    }
}
