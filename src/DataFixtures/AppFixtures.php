<?php

namespace App\DataFixtures;

use App\Entity\Annonce;
use App\Entity\Chambre;
use App\Entity\Charge;
use App\Entity\Colocation;
use App\Entity\Loyer;
use App\Entity\Message;
use App\Entity\Notification;
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

        // Annonces
        $annonce1 = new Annonce();
        $annonce1->setTitre('Chambre moderne Paris 11ème');
        $annonce1->setDescription("Belle chambre de 18m² dans une colocation de 3 personnes. Cuisine équipée, salon commun, double vitrage. Proche métro Nation (ligne 1, 2, 9).");
        $annonce1->setPrix(650.00);
        $annonce1->setLocalisation('Paris 11ème');
        $annonce1->setStatut(Annonce::STATUT_DISPONIBLE);
        $annonce1->setColocation($colocation);
        $annonce1->setMetaDescription('Chambre meublée 18m² dans colocation Paris 11ème, 650€/mois, proche Nation.');
        $manager->persist($annonce1);

        $annonce2 = new Annonce();
        $annonce2->setTitre('Grande chambre lumineuse Lyon');
        $annonce2->setDescription("Chambre de 20m² très lumineuse, orientée sud. Colocation calme et conviviale. Parking disponible.");
        $annonce2->setPrix(520.00);
        $annonce2->setLocalisation('Lyon 3ème');
        $annonce2->setStatut(Annonce::STATUT_DISPONIBLE);
        $annonce2->setColocation($colocation2);
        $annonce2->setMetaDescription('Grande chambre 20m² colocation Lyon, 520€/mois. Calme et lumineux.');
        $manager->persist($annonce2);

        $annonce3 = new Annonce();
        $annonce3->setTitre('Chambre cosy Paris - déjà louée');
        $annonce3->setDescription("Chambre confortable dans une colocation de standing.");
        $annonce3->setPrix(700.00);
        $annonce3->setLocalisation('Paris 11ème');
        $annonce3->setStatut(Annonce::STATUT_INDISPONIBLE);
        $annonce3->setColocation($colocation);
        $annonce3->setMetaDescription('Chambre 700€/mois Paris 11ème colocation.');
        $manager->persist($annonce3);

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

        // Charges
        $charge1 = new Charge();
        $charge1->setType(Charge::TYPE_ELECTRICITE);
        $charge1->setDescription('Facture EDF ' . date('M Y'));
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
        $notif->setMessage('Votre loyer du mois de ' . date('F Y') . ' a bien été enregistré.');
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
