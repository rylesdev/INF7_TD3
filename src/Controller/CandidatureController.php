<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Entity\Loyer;
use App\Entity\Message;
use App\Entity\Notification;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\CandidatureRepository;
use App\Repository\ColocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CandidatureController extends AbstractController
{
    // ── Locataire ────────────────────────────────────────────────────────────

    #[Route('/locataire/candidature-form/{id}', name: 'app_locataire_candidature_form', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function candidatureForm(int $id, EntityManagerInterface $em, CandidatureRepository $candidatureRepo): Response
    {
        if ($this->isGranted('ROLE_PROPRIETAIRE')) {
            return $this->redirectToRoute('app_annonce_show', ['id' => $id]);
        }
        $annonce = $em->find(\App\Entity\Annonce::class, $id);
        if (!$annonce) throw $this->createNotFoundException();

        $dejaCandidat = $candidatureRepo->findOneByLocataireAnnonce($this->getUser()->getId(), $id) !== null;
        return $this->render('locataire/candidature_form.html.twig', [
            'annonce'      => $annonce,
            'dejaCandidat' => $dejaCandidat,
        ]);
    }

    #[Route('/locataire/candidater/{id}', name: 'app_locataire_candidater', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function candidater(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        CandidatureRepository $candidatureRepo,
        SluggerInterface $slugger
    ): Response {
        if ($this->isGranted('ROLE_PROPRIETAIRE')) {
            $this->addFlash('error', 'Un propriétaire ne peut pas candidater à une annonce.');
            return $this->redirectToRoute('app_annonce_show', ['id' => $id]);
        }

        if (!$this->isCsrfTokenValid('candidater_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $annonce = $em->find(\App\Entity\Annonce::class, $id);
        if (!$annonce) {
            throw $this->createNotFoundException();
        }

        if (!$annonce->isDisponible()) {
            $this->addFlash('error', 'Cette annonce n\'est plus disponible.');
            return $this->redirectToRoute('app_annonce_show', ['id' => $id]);
        }

        $chambresLibres = $annonce->getColocation()->getChambres()
            ->filter(fn($c) => $c->getLocataire() === null)
            ->count();
        if ($chambresLibres === 0) {
            $this->addFlash('error', 'Cette colocation n\'a plus de chambre disponible.');
            return $this->redirectToRoute('app_annonce_show', ['id' => $id]);
        }

        $user = $this->getUser();
        if ($candidatureRepo->findOneByLocataireAnnonce($user->getId(), $id)) {
            $this->addFlash('warning', 'Vous avez déjà envoyé une candidature pour cette annonce.');
            return $this->redirectToRoute('app_annonce_show', ['id' => $id]);
        }

        $pieceIdentiteFile    = $request->files->get('piece_identite');
        $justificatifRevenuFile = $request->files->get('justificatif_revenu');

        if (!$pieceIdentiteFile || !$justificatifRevenuFile) {
            $this->addFlash('error', 'La pièce d\'identité et le justificatif de revenus sont obligatoires.');
            return $this->redirectToRoute('app_locataire_candidature_form', ['id' => $id]);
        }

        $candidature = new Candidature();
        $candidature->setLocataire($user);
        $candidature->setAnnonce($annonce);

        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/candidatures/';
        $fichierLabels = [];
        foreach (['pieceIdentite' => [$pieceIdentiteFile, 'Pièce d\'identité'], 'justificatifRevenu' => [$justificatifRevenuFile, 'Justificatif de revenus']] as $field => [$file, $label]) {
            try {
                $filename = $slugger->slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                    . '-' . bin2hex(random_bytes(4)) . '.' . $file->guessExtension();
                $file->move($uploadDir, $filename);
                $setter = 'set' . ucfirst($field);
                $candidature->$setter($filename);
                $fichierLabels[] = $label . ' : ' . $file->getClientOriginalName();
            } catch (FileException) {}
        }

        $em->persist($candidature);

        $colocation = $annonce->getColocation();
        $proprio    = $colocation?->getProprietaire();
        if ($proprio) {
            $pjTexte = count($fichierLabels) > 0 ? "\n\nPièces jointes :\n- " . implode("\n- ", $fichierLabels) : '';
            $msgAuto = new Message();
            $msgAuto->setExpediteur($user);
            $msgAuto->setDestinataire($proprio);
            $msgAuto->setColocation($colocation);
            $msgAuto->setContenu($user->getNomComplet() . ' a candidaté pour votre annonce ' . $annonce->getTitre() . $pjTexte);
            $msgAuto->setAutomatique(true);
            $msgAuto->setLien($this->generateUrl('app_proprietaire_candidatures'));
            $em->persist($msgAuto);

            $notifProprio = new Notification();
            $notifProprio->setUser($proprio);
            $notifProprio->setType(Notification::TYPE_INFO);
            $notifProprio->setTitre('Nouvelle candidature de ' . $user->getNomComplet());
            $notifProprio->setMessage($user->getNomComplet() . ' a candidaté pour votre annonce « ' . $annonce->getTitre() . ' ».');
            $notifProprio->setLien($this->generateUrl('app_proprietaire_candidatures'));
            $em->persist($notifProprio);
        }

        $notifLocataire = new Notification();
        $notifLocataire->setUser($user);
        $notifLocataire->setType(Notification::TYPE_INFO);
        $notifLocataire->setTitre('Candidature envoyée');
        $notifLocataire->setMessage('Votre candidature pour « ' . $annonce->getTitre() . ' » a bien été envoyée. En attente de réponse du propriétaire.');
        $notifLocataire->setLien($this->generateUrl('app_locataire_messagerie_annonce', ['id' => $annonce->getId()]));
        $em->persist($notifLocataire);

        $em->flush();
        $this->addFlash('success', 'Votre candidature a été envoyée. Le propriétaire a été notifié.');
        return $this->redirectToRoute('app_annonce_show', ['id' => $id]);
    }

    #[Route('/locataire/candidatures', name: 'app_locataire_candidatures')]
    #[IsGranted('ROLE_USER')]
    public function mesCandidatures(CandidatureRepository $repo): Response
    {
        return $this->render('locataire/candidatures.html.twig', [
            'candidatures' => $repo->findByLocataire($this->getUser()->getId()),
        ]);
    }

    // ── Propriétaire ─────────────────────────────────────────────────────────

    #[Route('/proprietaire/candidatures', name: 'app_proprietaire_candidatures')]
    #[IsGranted('ROLE_PROPRIETAIRE')]
    public function candidaturesRecues(CandidatureRepository $repo): Response
    {
        return $this->render('proprietaire/candidatures.html.twig', [
            'candidatures' => $repo->findByProprietaire($this->getUser()->getId()),
        ]);
    }

    #[Route('/proprietaire/candidatures/{id}/accepter', name: 'app_proprietaire_candidature_accepter', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_PROPRIETAIRE')]
    public function accepter(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        CandidatureRepository $candidatureRepo
    ): Response {
        $candidature = $candidatureRepo->find($id);
        if (!$candidature) {
            throw $this->createNotFoundException();
        }

        $colocation = $candidature->getAnnonce()->getColocation();
        if ($colocation->getProprietaire()?->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        if (!$candidature->isEnAttente()) {
            $this->addFlash('warning', 'Cette candidature a déjà été traitée.');
            return $this->redirectToRoute('app_proprietaire_candidatures');
        }

        $chambresDisponibles = $colocation->getChambres()->filter(fn($c) => $c->getLocataire() === null)->getValues();

        if (empty($chambresDisponibles)) {
            $this->addFlash('error', 'Aucune chambre disponible dans cette colocation.');
            return $this->redirectToRoute('app_proprietaire_candidatures');
        }

        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('accepter_' . $id, $request->request->get('_token'))) {
                throw $this->createAccessDeniedException();
            }

            $chambreId = (int) $request->request->get('chambre_id');
            $chambre   = null;
            foreach ($chambresDisponibles as $c) {
                if ($c->getId() === $chambreId) {
                    $chambre = $c;
                    break;
                }
            }

            if (!$chambre) {
                $this->addFlash('error', 'Chambre invalide.');
                return $this->redirectToRoute('app_proprietaire_candidature_accepter', ['id' => $id]);
            }

            $locataire = $candidature->getLocataire();
            $chambre->setLocataire($locataire);

            $loyer = new Loyer();
            $loyer->setChambre($chambre);
            $loyer->setColocation($colocation);
            $loyer->setMontant((string) $chambre->getLoyerMensuel());
            $loyer->setMois((int) date('n'));
            $loyer->setAnnee((int) date('Y'));
            $loyer->setStatut('impayé');
            $loyer->setDateEcheance((new \DateTimeImmutable())->modify('last day of this month'));
            $em->persist($loyer);

            $candidature->setStatut(Candidature::STATUT_ACCEPTE);

            $msgAccept = new Message();
            $msgAccept->setExpediteur($this->getUser());
            $msgAccept->setDestinataire($locataire);
            $msgAccept->setColocation($colocation);
            $msgAccept->setContenu('Votre candidature pour « ' . $candidature->getAnnonce()->getTitre() . ' » a été acceptée. Vous êtes assigné(e) à la chambre « ' . $chambre->getNom() . ' ». Bienvenue !');
            $msgAccept->setAutomatique(true);
            $msgAccept->setLien($this->generateUrl('app_locataire_dashboard'));
            $em->persist($msgAccept);

            $notif = new Notification();
            $notif->setUser($locataire);
            $notif->setType(Notification::TYPE_INFO);
            $notif->setTitre('Candidature acceptée !');
            $notif->setMessage('Votre candidature pour « ' . $candidature->getAnnonce()->getTitre() . ' » a été acceptée.');
            $notif->setLien($this->generateUrl('app_locataire_messagerie'));
            $em->persist($notif);

            // Si plus aucune chambre libre, passer toutes les annonces de la colocation en indisponible
            $chambresLibresRestantes = $colocation->getChambres()
                ->filter(fn($c) => $c->getLocataire() === null)
                ->count();
            if ($chambresLibresRestantes === 0) {
                foreach ($colocation->getAnnonces() as $a) {
                    $a->setStatut(\App\Entity\Annonce::STATUT_INDISPONIBLE);
                }
            }

            $em->flush();
            $this->addFlash('success', $locataire->getNomComplet() . ' a été assigné(e) à la chambre « ' . $chambre->getNom() . ' ».');
            return $this->redirectToRoute('app_proprietaire_candidatures');
        }

        return $this->render('proprietaire/candidature_accepter.html.twig', [
            'candidature'        => $candidature,
            'chambresDisponibles' => $chambresDisponibles,
        ]);
    }

    #[Route('/proprietaire/candidatures/{id}/refuser', name: 'app_proprietaire_candidature_refuser', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_PROPRIETAIRE')]
    public function refuser(
        int $id,
        Request $request,
        EntityManagerInterface $em,
        CandidatureRepository $candidatureRepo
    ): Response {
        $candidature = $candidatureRepo->find($id);
        if (!$candidature) {
            throw $this->createNotFoundException();
        }

        $colocation = $candidature->getAnnonce()->getColocation();
        if ($colocation->getProprietaire()?->getId() !== $this->getUser()->getId()) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->isCsrfTokenValid('refuser_' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $candidature->setStatut(Candidature::STATUT_REFUSE);

        $locataireRefuse = $candidature->getLocataire();
        $msgRefus = new Message();
        $msgRefus->setExpediteur($this->getUser());
        $msgRefus->setDestinataire($locataireRefuse);
        $msgRefus->setColocation($candidature->getAnnonce()->getColocation());
        $msgRefus->setContenu('Votre candidature pour « ' . $candidature->getAnnonce()->getTitre() . ' » n\'a pas été retenue. Bonne continuation dans vos recherches.');
        $msgRefus->setAutomatique(true);
        $msgRefus->setLien($this->generateUrl('app_annonces'));
        $em->persist($msgRefus);

        $notif = new Notification();
        $notif->setUser($locataireRefuse);
        $notif->setType(Notification::TYPE_INFO);
        $notif->setTitre('Candidature non retenue');
        $notif->setMessage('Votre candidature pour « ' . $candidature->getAnnonce()->getTitre() . ' » n\'a pas été retenue.');
        $notif->setLien($this->generateUrl('app_locataire_candidatures'));
        $em->persist($notif);

        $em->flush();
        $this->addFlash('info', 'Candidature refusée.');
        return $this->redirectToRoute('app_proprietaire_candidatures');
    }
}
