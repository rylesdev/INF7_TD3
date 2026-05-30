<?php

namespace App\DataFixtures;

use App\Entity\Annonce;
use App\Entity\Chambre;
use App\Entity\Charge;
use App\Entity\Colocation;
use App\Entity\Loyer;
use App\Entity\Message;
use App\Entity\Notification;
use App\Entity\Quittance;
use App\Entity\Tache;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $moisFr = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];

        // Users
        $proprio = new User();
        $proprio->setPrenom('Jean');
        $proprio->setNom('Dupont');
        $proprio->setEmail('proprio@colocation.com');
        $proprio->setRoles(['ROLE_PROPRIETAIRE']);
        $proprio->setTelephone('0601020304');
        $proprio->setPassword($this->hasher->hashPassword($proprio, 'Proprio1234!'));
        $manager->persist($proprio);

        $locataire = new User();
        $locataire->setPrenom('Marie');
        $locataire->setNom('Martin');
        $locataire->setEmail('locataire@colocation.com');
        $locataire->setRoles(['ROLE_USER']);
        $locataire->setTelephone('0607080910');
        $locataire->setPassword($this->hasher->hashPassword($locataire, 'Locataire1234!'));
        $manager->persist($locataire);

        $locataire2 = new User();
        $locataire2->setPrenom('Pierre');
        $locataire2->setNom('Leroy');
        $locataire2->setEmail('locataire2@colocation.com');
        $locataire2->setRoles(['ROLE_USER']);
        $locataire2->setTelephone('0611121314');
        $locataire2->setPassword($this->hasher->hashPassword($locataire2, 'Locataire1234!'));
        $manager->persist($locataire2);

        // Colocation
        $colocation = new Colocation();
        $colocation->setNom('Les Lilas');
        $colocation->setAdresse('12 rue des Lilas');
        $colocation->setVille('Paris');
        $colocation->setCodePostal('75011');
        $colocation->setLoyer(1800.00);
        $colocation->setDescription('Belle colocation en plein cœur de Paris, proche des transports.');
        $colocation->setLatitude(48.8566);
        $colocation->setLongitude(2.3522);
        $colocation->setProprietaire($proprio);
        $manager->persist($colocation);

        $colocation2 = new Colocation();
        $colocation2->setNom('Villa Soleil');
        $colocation2->setAdresse('5 avenue du Soleil');
        $colocation2->setVille('Lyon');
        $colocation2->setCodePostal('69003');
        $colocation2->setLoyer(1200.00);
        $colocation2->setDescription('Colocation spacieuse et lumineuse à Lyon.');
        $colocation2->setLatitude(45.7640);
        $colocation2->setLongitude(4.8357);
        $colocation2->setProprietaire($proprio);
        $manager->persist($colocation2);

        // Chambres
        $chambre1 = new Chambre();
        $chambre1->setNom('Chambre A');
        $chambre1->setSurface('18.5');
        $chambre1->setLoyerMensuel(650.00);
        $chambre1->setColocation($colocation);
        $chambre1->setLocataire($locataire);
        $manager->persist($chambre1);

        $chambre2 = new Chambre();
        $chambre2->setNom('Chambre B');
        $chambre2->setSurface('14.0');
        $chambre2->setLoyerMensuel(520.00);
        $chambre2->setColocation($colocation);
        $chambre2->setLocataire($locataire2);
        $manager->persist($chambre2);

        $chambre3 = new Chambre();
        $chambre3->setNom('Chambre C');
        $chambre3->setSurface('20.0');
        $chambre3->setLoyerMensuel(700.00);
        $chambre3->setColocation($colocation);
        $manager->persist($chambre3);

        // Colocations supplémentaires
        $colocation3 = new Colocation();
        $colocation3->setNom('Le Palais');
        $colocation3->setAdresse('8 cours du Maréchal Foch');
        $colocation3->setVille('Bordeaux');
        $colocation3->setCodePostal('33000');
        $colocation3->setLoyer(1350.00);
        $colocation3->setDescription('Colocation dans un immeuble haussmannien rénové, proche gare Saint-Jean.');
        $colocation3->setLatitude(44.8378);
        $colocation3->setLongitude(-0.5792);
        $colocation3->setProprietaire($proprio);
        $manager->persist($colocation3);

        $colocation4 = new Colocation();
        $colocation4->setNom('La Calanque');
        $colocation4->setAdresse('3 boulevard de la Corderie');
        $colocation4->setVille('Marseille');
        $colocation4->setCodePostal('13007');
        $colocation4->setLoyer(1100.00);
        $colocation4->setDescription('Colocation avec vue mer, à deux pas des calanques.');
        $colocation4->setLatitude(43.2965);
        $colocation4->setLongitude(5.3698);
        $colocation4->setProprietaire($proprio);
        $manager->persist($colocation4);

        $colocation5 = new Colocation();
        $colocation5->setNom('Les Capitouls');
        $colocation5->setAdresse('22 rue du Taur');
        $colocation5->setVille('Toulouse');
        $colocation5->setCodePostal('31000');
        $colocation5->setLoyer(1050.00);
        $colocation5->setDescription('Grande colocation étudiante en plein centre de Toulouse, à 5 min du Capitole.');
        $colocation5->setLatitude(43.6047);
        $colocation5->setLongitude(1.4442);
        $colocation5->setProprietaire($proprio);
        $manager->persist($colocation5);

        // Annonces
        $annonce1 = new Annonce();
        $annonce1->setTitre('Chambre moderne Paris 11ème');
        $annonce1->setDescription("Belle chambre de 18m² dans une colocation de 3 personnes. Cuisine équipée, salon commun, double vitrage. Proche métro Nation (ligne 1, 2, 9). Charges comprises, idéal jeune actif.");
        $annonce1->setPrix(650.00);
        $annonce1->setLocalisation('Paris 11ème');
        $annonce1->setStatut(Annonce::STATUT_DISPONIBLE);
        $annonce1->setColocation($colocation);
        $annonce1->setMetaDescription('Chambre meublée 18m² dans colocation Paris 11ème, 650€/mois, proche Nation.');
        $manager->persist($annonce1);

        $annonce2 = new Annonce();
        $annonce2->setTitre('Grande chambre lumineuse Lyon 3ème');
        $annonce2->setDescription("Chambre de 20m² très lumineuse, orientée sud dans une belle colocation de 4 personnes. Cuisine équipée, balcon partagé, fibre optique. Parking disponible en sous-sol.");
        $annonce2->setPrix(520.00);
        $annonce2->setLocalisation('Lyon 3ème');
        $annonce2->setStatut(Annonce::STATUT_DISPONIBLE);
        $annonce2->setColocation($colocation2);
        $annonce2->setMetaDescription('Grande chambre 20m² colocation Lyon, 520€/mois. Calme et lumineux.');
        $manager->persist($annonce2);

        $annonce3 = new Annonce();
        $annonce3->setTitre('Chambre cosy Paris 11ème — non disponible');
        $annonce3->setDescription("Chambre confortable dans une colocation de standing. Parquet ancien, hauteur sous plafond 3m.");
        $annonce3->setPrix(700.00);
        $annonce3->setLocalisation('Paris 11ème');
        $annonce3->setStatut(Annonce::STATUT_INDISPONIBLE);
        $annonce3->setColocation($colocation);
        $annonce3->setMetaDescription('Chambre 700€/mois Paris 11ème colocation.');
        $manager->persist($annonce3);

        $annonce4 = new Annonce();
        $annonce4->setTitre('Chambre calme Bordeaux centre');
        $annonce4->setDescription("Chambre de 16m² dans un appartement haussmannien entièrement rénové. Colocation de 3 personnes, ambiance jeune professionnelle. À 10 min à pied de la gare Saint-Jean, tram à 2 minutes.");
        $annonce4->setPrix(490.00);
        $annonce4->setLocalisation('Bordeaux centre');
        $annonce4->setStatut(Annonce::STATUT_DISPONIBLE);
        $annonce4->setColocation($colocation3);
        $annonce4->setMetaDescription('Chambre 16m² colocation Bordeaux centre, 490€/mois, proche gare.');
        $manager->persist($annonce4);

        $annonce5 = new Annonce();
        $annonce5->setTitre('Chambre vue mer Marseille 7ème');
        $annonce5->setDescription("Chambre de 14m² avec fenêtre donnant sur la Méditerranée. Colocation de 4 personnes, esprit bonne humeur garanti ! Proche plages du Prado et des calanques. Idéal pour un séjour estival ou une installation durable.");
        $annonce5->setPrix(450.00);
        $annonce5->setLocalisation('Marseille 7ème');
        $annonce5->setStatut(Annonce::STATUT_DISPONIBLE);
        $annonce5->setColocation($colocation4);
        $annonce5->setMetaDescription('Chambre vue mer 14m² colocation Marseille, 450€/mois.');
        $manager->persist($annonce5);

        $annonce6 = new Annonce();
        $annonce6->setTitre('Chambre étudiante Toulouse hyper-centre');
        $annonce6->setDescription("Chambre de 12m² dans une grande colocation étudiante de 5 chambres. Situation idéale : à 5 minutes du Capitole, 10 minutes de l'université Paul Sabatier. Wifi haut débit, machine à laver, vélos partagés.");
        $annonce6->setPrix(380.00);
        $annonce6->setLocalisation('Toulouse centre');
        $annonce6->setStatut(Annonce::STATUT_DISPONIBLE);
        $annonce6->setColocation($colocation5);
        $annonce6->setMetaDescription('Chambre étudiante 12m² colocation Toulouse centre, 380€/mois.');
        $manager->persist($annonce6);

        $annonce7 = new Annonce();
        $annonce7->setTitre('Chambre spacieuse Lyon — proche Part-Dieu');
        $annonce7->setDescription("Chambre de 22m² dans une colocation rénovée. Cuisine américaine ouverte sur le salon. Idéal jeune actif travaillant à la Part-Dieu ou au quartier d'affaires. Fibre, parking vélos, cave individuelle.");
        $annonce7->setPrix(580.00);
        $annonce7->setLocalisation('Lyon 3ème');
        $annonce7->setStatut(Annonce::STATUT_DISPONIBLE);
        $annonce7->setColocation($colocation2);
        $annonce7->setMetaDescription('Chambre 22m² colocation Lyon Part-Dieu, 580€/mois.');
        $manager->persist($annonce7);

        $annonce8 = new Annonce();
        $annonce8->setTitre('Grande chambre Paris – ambiance cosy');
        $annonce8->setDescription("Chambre de 24m² avec dressing intégré dans un appartement de 90m². Colocation de 4 personnes, toutes jeunes actives. Cuisine entièrement équipée, terrasse commune de 15m². Métro Père Lachaise à 3 min.");
        $annonce8->setPrix(780.00);
        $annonce8->setLocalisation('Paris 20ème');
        $annonce8->setStatut(Annonce::STATUT_DISPONIBLE);
        $annonce8->setColocation($colocation);
        $annonce8->setMetaDescription('Grande chambre 24m² avec terrasse, colocation Paris 20ème, 780€/mois.');
        $manager->persist($annonce8);

        // Loyers
        $loyer1 = new Loyer();
        $loyer1->setMontant(650.00);
        $loyer1->setMois((int) date('n'));
        $loyer1->setAnnee((int) date('Y'));
        $loyer1->setDateEcheance(new \DateTimeImmutable('first day of this month'));
        $loyer1->setDatePaiement(new \DateTimeImmutable('-5 days'));
        $loyer1->setStatut(Loyer::STATUT_PAYE);
        $loyer1->setColocation($colocation);
        $loyer1->setChambre($chambre1);
        $manager->persist($loyer1);

        $loyer2 = new Loyer();
        $loyer2->setMontant(520.00);
        $loyer2->setMois((int) date('n'));
        $loyer2->setAnnee((int) date('Y'));
        $loyer2->setDateEcheance(new \DateTimeImmutable('first day of this month'));
        $loyer2->setStatut(Loyer::STATUT_IMPAYE);
        $loyer2->setColocation($colocation);
        $loyer2->setChambre($chambre2);
        $manager->persist($loyer2);

        // Quittance pour loyer1 (payé)
        $debut = \DateTimeImmutable::createFromFormat('Y-m-d', date('Y') . '-' . date('m') . '-01');
        $fin   = $debut->modify('last day of this month');
        $quittance1 = new Quittance();
        $quittance1->setLoyer($loyer1);
        $quittance1->setMontantLoyer('650.00');
        $quittance1->setMontantCharges('0.00');
        $quittance1->setMontantTotal('650.00');
        $quittance1->setPeriodeDebut($debut);
        $quittance1->setPeriodeFin($fin);
        $manager->persist($quittance1);

        // Charges
        $charge1 = new Charge();
        $charge1->setType(Charge::TYPE_ELECTRICITE);
        $charge1->setDescription('Facture EDF ' . $moisFr[(int)date('n') - 1] . ' ' . date('Y'));
        $charge1->setMontant(120.00);
        $charge1->setDate(new \DateTimeImmutable('-10 days'));
        $charge1->setColocation($colocation);
        $manager->persist($charge1);

        $charge2 = new Charge();
        $charge2->setType(Charge::TYPE_INTERNET);
        $charge2->setDescription('Abonnement internet');
        $charge2->setMontant(35.00);
        $charge2->setDate(new \DateTimeImmutable('-5 days'));
        $charge2->setColocation($colocation);
        $manager->persist($charge2);

        // Messages
        $message1 = new Message();
        $message1->setContenu('Bonjour, je voudrais savoir si le loyer du mois est bien enregistré.');
        $message1->setExpediteur($locataire);
        $message1->setDestinataire($proprio);
        $message1->setColocation($colocation);
        $manager->persist($message1);

        $message2 = new Message();
        $message2->setContenu('Oui bonjour Marie, tout est bien noté. Bonne journée !');
        $message2->setExpediteur($proprio);
        $message2->setDestinataire($locataire);
        $message2->setColocation($colocation);
        $manager->persist($message2);

        // Notifications
        $notif = new Notification();
        $notif->setTitre('Loyer enregistré');
        $notif->setMessage('Votre loyer du mois de ' . $moisFr[(int)date('n') - 1] . ' ' . date('Y') . ' a bien été enregistré.');
        $notif->setType('loyer');
        $notif->setLue(false);
        $notif->setUser($locataire);
        $manager->persist($notif);

        // Taches
        $tache1 = new Tache();
        $tache1->setTitre('Faire la vaisselle');
        $tache1->setType(Tache::TYPE_VAISSELLE);
        $tache1->setStatut(Tache::STATUT_A_FAIRE);
        $tache1->setColocation($colocation);
        $tache1->setAssigne($locataire);
        $manager->persist($tache1);

        $tache2 = new Tache();
        $tache2->setTitre('Nettoyer les parties communes');
        $tache2->setType(Tache::TYPE_MENAGE);
        $tache2->setStatut(Tache::STATUT_EN_COURS);
        $tache2->setColocation($colocation);
        $tache2->setAssigne($locataire2);
        $manager->persist($tache2);

        $manager->flush();
    }
}
