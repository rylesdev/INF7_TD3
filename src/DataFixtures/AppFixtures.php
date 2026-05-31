<?php

namespace App\DataFixtures;

use App\Entity\Annonce;
use App\Entity\EvaluationProprietaire;
use App\Entity\Chambre;
use App\Entity\Charge;
use App\Entity\Colocation;
use App\Entity\Loyer;
use App\Entity\Message;
use App\Entity\Notification;
use App\Entity\PhotoAnnonce;
use App\Entity\Quittance;
use App\Entity\Tache;
use App\Entity\Tantieme;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    private function generatePlaceholderImage(string $uploadsDir, string $filename, string $city, string $label, string $hex): void
    {
        $path = $uploadsDir . $filename;
        if (file_exists($path)) {
            return;
        }

        $w = 800; $h = 500;
        $img = imagecreatetruecolor($w, $h);

        $r = hexdec(substr($hex, 1, 2));
        $g = hexdec(substr($hex, 3, 2));
        $b = hexdec(substr($hex, 5, 2));

        $bg      = imagecolorallocate($img, $r, $g, $b);
        $dark    = imagecolorallocate($img, max(0, $r - 50), max(0, $g - 50), max(0, $b - 50));
        $darker  = imagecolorallocate($img, max(0, $r - 80), max(0, $g - 80), max(0, $b - 80));
        $light   = imagecolorallocate($img, min(255, $r + 40), min(255, $g + 40), min(255, $b + 40));
        $white   = imagecolorallocate($img, 255, 255, 255);
        $overlay = imagecolorallocate($img, max(0, $r - 20), max(0, $g - 20), max(0, $b - 20));

        imagefill($img, 0, 0, $bg);

        // Sol
        imagefilledrectangle($img, 0, 360, $w, $h, $dark);

        // Mur du fond
        imagefilledrectangle($img, 0, 0, $w, 360, $bg);

        // Fenêtre gauche
        imagefilledrectangle($img, 80, 60, 240, 280, $light);
        imagefilledrectangle($img, 88, 68, 232, 272, $darker);
        imageline($img, 160, 68, 160, 272, $light);
        imageline($img, 88, 170, 232, 170, $light);

        // Fenêtre droite
        imagefilledrectangle($img, 520, 60, 700, 280, $light);
        imagefilledrectangle($img, 528, 68, 692, 272, $darker);
        imageline($img, 610, 68, 610, 272, $light);
        imageline($img, 528, 170, 692, 170, $light);

        // Porte
        imagefilledrectangle($img, 340, 160, 460, 360, $dark);
        imagefilledrectangle($img, 348, 168, 452, 360, $darker);
        imagefilledellipse($img, 445, 265, 12, 12, $light);

        // Plafond (ligne)
        imageline($img, 0, 30, $w, 30, $dark);

        // Bandeau texte en bas
        imagefilledrectangle($img, 0, 415, $w, $h, $overlay);

        // Texte avec font TTF si disponible, sinon built-in
        $font = 'C:/Windows/Fonts/arialbd.ttf';
        if (function_exists('imagettftext') && file_exists($font)) {
            imagettftext($img, 26, 0, 40, 460, $white, $font, $city . ' - ' . $label);
        } else {
            imagestring($img, 5, 40, 445, $city . ' - ' . $label, $white);
        }

        imagejpeg($img, $path, 88);
        imagedestroy($img);
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

        $proprio2 = new User();
        $proprio2->setPrenom('Sophie');
        $proprio2->setNom('Bernard');
        $proprio2->setEmail('proprio2@colocation.com');
        $proprio2->setRoles(['ROLE_PROPRIETAIRE']);
        $proprio2->setTelephone('0612131415');
        $proprio2->setPassword($this->hasher->hashPassword($proprio2, 'Proprio1234!'));
        $manager->persist($proprio2);

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
        $colocation->setLatitude(48.8592);
        $colocation->setLongitude(2.3750);
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

        // Chambres Villa Soleil (Lyon)
        $chambre4 = new Chambre();
        $chambre4->setNom('Chambre 1'); $chambre4->setSurface('20.0'); $chambre4->setLoyerMensuel(520.00);
        $chambre4->setColocation($colocation2); $manager->persist($chambre4);

        $chambre5 = new Chambre();
        $chambre5->setNom('Chambre 2'); $chambre5->setSurface('22.0'); $chambre5->setLoyerMensuel(580.00);
        $chambre5->setColocation($colocation2); $manager->persist($chambre5);

        $chambre6 = new Chambre();
        $chambre6->setNom('Chambre 3'); $chambre6->setSurface('16.0'); $chambre6->setLoyerMensuel(460.00);
        $chambre6->setColocation($colocation2); $manager->persist($chambre6);

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

        // Chambres Le Palais (Bordeaux)
        $chambre7 = new Chambre();
        $chambre7->setNom('Chambre A'); $chambre7->setSurface('16.0'); $chambre7->setLoyerMensuel(490.00);
        $chambre7->setColocation($colocation3); $manager->persist($chambre7);

        $chambre8 = new Chambre();
        $chambre8->setNom('Chambre B'); $chambre8->setSurface('14.0'); $chambre8->setLoyerMensuel(450.00);
        $chambre8->setColocation($colocation3); $manager->persist($chambre8);

        $chambre9 = new Chambre();
        $chambre9->setNom('Chambre C'); $chambre9->setSurface('18.0'); $chambre9->setLoyerMensuel(520.00);
        $chambre9->setColocation($colocation3); $manager->persist($chambre9);

        // Chambres La Calanque (Marseille)
        $chambre10 = new Chambre();
        $chambre10->setNom('Chambre Mer'); $chambre10->setSurface('14.0'); $chambre10->setLoyerMensuel(450.00);
        $chambre10->setColocation($colocation4); $manager->persist($chambre10);

        $chambre11 = new Chambre();
        $chambre11->setNom('Chambre Jardin'); $chambre11->setSurface('12.0'); $chambre11->setLoyerMensuel(400.00);
        $chambre11->setColocation($colocation4); $manager->persist($chambre11);

        // Chambres Les Capitouls (Toulouse)
        $chambre12 = new Chambre();
        $chambre12->setNom('Chambre 1'); $chambre12->setSurface('12.0'); $chambre12->setLoyerMensuel(380.00);
        $chambre12->setColocation($colocation5); $manager->persist($chambre12);

        $chambre13 = new Chambre();
        $chambre13->setNom('Chambre 2'); $chambre13->setSurface('11.0'); $chambre13->setLoyerMensuel(360.00);
        $chambre13->setColocation($colocation5); $manager->persist($chambre13);

        $chambre14 = new Chambre();
        $chambre14->setNom('Chambre 3'); $chambre14->setSurface('13.0'); $chambre14->setLoyerMensuel(390.00);
        $chambre14->setColocation($colocation5); $manager->persist($chambre14);

        $chambre15 = new Chambre();
        $chambre15->setNom('Chambre 4'); $chambre15->setSurface('12.5'); $chambre15->setLoyerMensuel(375.00);
        $chambre15->setColocation($colocation5); $manager->persist($chambre15);

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
        $annonce3->setTitre('Chambre cosy Paris 11ème - non disponible');
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
        $annonce7->setTitre('Chambre spacieuse Lyon - proche Part-Dieu');
        $annonce7->setDescription("Chambre de 22m² dans une colocation rénovée. Cuisine américaine ouverte sur le salon. Idéal jeune actif travaillant à la Part-Dieu ou au quartier d'affaires. Fibre, parking vélos, cave individuelle.");
        $annonce7->setPrix(580.00);
        $annonce7->setLocalisation('Lyon 3ème');
        $annonce7->setStatut(Annonce::STATUT_DISPONIBLE);
        $annonce7->setColocation($colocation2);
        $annonce7->setMetaDescription('Chambre 22m² colocation Lyon Part-Dieu, 580€/mois.');
        $manager->persist($annonce7);

        $annonce8 = new Annonce();
        $annonce8->setTitre('Grande chambre Paris - ambiance cosy');
        $annonce8->setDescription("Chambre de 24m² avec dressing intégré dans un appartement de 90m². Colocation de 4 personnes, toutes jeunes actives. Cuisine entièrement équipée, terrasse commune de 15m². Métro Oberkampf à 3 min.");
        $annonce8->setPrix(780.00);
        $annonce8->setLocalisation('Paris 11ème');
        $annonce8->setStatut(Annonce::STATUT_DISPONIBLE);
        $annonce8->setColocation($colocation);
        $annonce8->setMetaDescription('Grande chambre 24m² avec terrasse, colocation Paris 11ème, 780€/mois.');
        $manager->persist($annonce8);

        // Photos des annonces
        $uploadsDir = __DIR__ . '/../../public/uploads/annonces/';

        $photosData = [
            [$annonce1, 'annonce1_paris11.jpg',   'Paris 11ème',    'Chambre moderne', '#2c3e50'],
            [$annonce2, 'annonce2_lyon3.jpg',      'Lyon 3ème',      'Grande chambre',  '#c0392b'],
            [$annonce3, 'annonce3_paris_cosy.jpg', 'Paris 11ème',    'Chambre cosy',    '#7f8c8d'],
            [$annonce4, 'annonce4_bordeaux.jpg',   'Bordeaux',       'Chambre calme',   '#8e44ad'],
            [$annonce5, 'annonce5_marseille.jpg',  'Marseille',      'Vue mer',         '#16a085'],
            [$annonce6, 'annonce6_toulouse.jpg',   'Toulouse',       'Chambre étudiante','#e67e22'],
            [$annonce7, 'annonce7_lyon_pd.jpg',    'Lyon Part-Dieu', 'Spacieuse',       '#2980b9'],
            [$annonce8, 'annonce8_paris20.jpg',    'Paris 20ème',    'Cosy terrasse',   '#27ae60'],
        ];

        foreach ($photosData as [$annonce, $filename, $city, $label, $hex]) {
            $this->generatePlaceholderImage($uploadsDir, $filename, $city, $label, $hex);

            $photo = new PhotoAnnonce();
            $photo->setFilename($filename);
            $photo->setAlt($city . ' - ' . $label);
            $photo->setPosition(0);
            $photo->setAnnonce($annonce);
            $manager->persist($photo);
        }

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

        // Colocations supplémentaires pour nouvelles annonces
        $colocation6 = new Colocation();
        $colocation6->setNom('L\'Atlantique');
        $colocation6->setAdresse('14 rue de Verdun');
        $colocation6->setVille('Nantes');
        $colocation6->setCodePostal('44000');
        $colocation6->setLoyer(980.00);
        $colocation6->setDescription('Belle colocation près du centre historique de Nantes.');
        $colocation6->setLatitude(47.2184);
        $colocation6->setLongitude(-1.5536);
        $colocation6->setProprietaire($proprio2);
        $manager->persist($colocation6);

        $colocation7 = new Colocation();
        $colocation7->setNom('Le Mistral');
        $colocation7->setAdresse('5 rue du Faubourg Figuerolles');
        $colocation7->setVille('Montpellier');
        $colocation7->setCodePostal('34000');
        $colocation7->setLoyer(1150.00);
        $colocation7->setDescription('Colocation ensoleillée à Montpellier, proche tram.');
        $colocation7->setLatitude(43.6119);
        $colocation7->setLongitude(3.8772);
        $colocation7->setProprietaire($proprio2);
        $manager->persist($colocation7);

        $colocation8 = new Colocation();
        $colocation8->setNom('La Cathédrale');
        $colocation8->setAdresse('3 rue des Frères');
        $colocation8->setVille('Strasbourg');
        $colocation8->setCodePostal('67000');
        $colocation8->setLoyer(1050.00);
        $colocation8->setDescription('Colocation au coeur de Strasbourg, à 5 min de la cathédrale.');
        $colocation8->setLatitude(48.5734);
        $colocation8->setLongitude(7.7521);
        $colocation8->setProprietaire($proprio2);
        $manager->persist($colocation8);

        $colocation9 = new Colocation();
        $colocation9->setNom('Le Breton');
        $colocation9->setAdresse('8 allée du Mail');
        $colocation9->setVille('Rennes');
        $colocation9->setCodePostal('35000');
        $colocation9->setLoyer(920.00);
        $colocation9->setDescription('Colocation étudiante à Rennes, proche du campus.');
        $colocation9->setLatitude(48.1173);
        $colocation9->setLongitude(-1.6778);
        $colocation9->setProprietaire($proprio2);
        $manager->persist($colocation9);

        // Chambres nouvelles colocations
        $chambre16 = new Chambre(); $chambre16->setNom('Chambre A'); $chambre16->setSurface('15.0'); $chambre16->setLoyerMensuel(430.00); $chambre16->setColocation($colocation6); $manager->persist($chambre16);
        $chambre17 = new Chambre(); $chambre17->setNom('Chambre B'); $chambre17->setSurface('17.0'); $chambre17->setLoyerMensuel(470.00); $chambre17->setColocation($colocation6); $manager->persist($chambre17);
        $chambre18 = new Chambre(); $chambre18->setNom('Chambre 1'); $chambre18->setSurface('16.0'); $chambre18->setLoyerMensuel(480.00); $chambre18->setColocation($colocation7); $manager->persist($chambre18);
        $chambre19 = new Chambre(); $chambre19->setNom('Chambre 2'); $chambre19->setSurface('14.0'); $chambre19->setLoyerMensuel(420.00); $chambre19->setColocation($colocation7); $manager->persist($chambre19);
        $chambre20 = new Chambre(); $chambre20->setNom('Chambre A'); $chambre20->setSurface('13.0'); $chambre20->setLoyerMensuel(400.00); $chambre20->setColocation($colocation8); $manager->persist($chambre20);
        $chambre21 = new Chambre(); $chambre21->setNom('Chambre B'); $chambre21->setSurface('15.0'); $chambre21->setLoyerMensuel(440.00); $chambre21->setColocation($colocation8); $manager->persist($chambre21);
        $chambre22 = new Chambre(); $chambre22->setNom('Chambre 1'); $chambre22->setSurface('14.0'); $chambre22->setLoyerMensuel(395.00); $chambre22->setColocation($colocation9); $manager->persist($chambre22);
        $chambre23 = new Chambre(); $chambre23->setNom('Chambre 2'); $chambre23->setSurface('12.0'); $chambre23->setLoyerMensuel(360.00); $chambre23->setColocation($colocation9); $manager->persist($chambre23);

        // Nouvelles annonces (9-16) — total 15 disponibles
        $annonce9 = new Annonce();
        $annonce9->setTitre('Chambre cosy Nantes centre');
        $annonce9->setDescription("Chambre de 15m² dans une colocation de 2 personnes en plein coeur de Nantes. Proche du château des Ducs et des transports.");
        $annonce9->setPrix(430.00); $annonce9->setLocalisation('Nantes centre'); $annonce9->setStatut(Annonce::STATUT_DISPONIBLE); $annonce9->setColocation($colocation6); $annonce9->setMetaDescription('Chambre 15m² colocation Nantes centre, 430€/mois.'); $manager->persist($annonce9);

        $annonce10 = new Annonce();
        $annonce10->setTitre('Grande chambre lumineuse Nantes');
        $annonce10->setDescription("Chambre de 17m² lumineuse dans une colocation moderne. Tram à 2 minutes, commerces à pied.");
        $annonce10->setPrix(470.00); $annonce10->setLocalisation('Nantes centre'); $annonce10->setStatut(Annonce::STATUT_DISPONIBLE); $annonce10->setColocation($colocation6); $annonce10->setMetaDescription('Chambre 17m² colocation Nantes, 470€/mois.'); $manager->persist($annonce10);

        $annonce11 = new Annonce();
        $annonce11->setTitre('Chambre ensoleillée Montpellier');
        $annonce11->setDescription("Chambre de 16m² dans une colocation ensoleillée au coeur de Montpellier. Proche fac de médecine et tramway.");
        $annonce11->setPrix(480.00); $annonce11->setLocalisation('Montpellier centre'); $annonce11->setStatut(Annonce::STATUT_DISPONIBLE); $annonce11->setColocation($colocation7); $annonce11->setMetaDescription('Chambre 16m² colocation Montpellier, 480€/mois.'); $manager->persist($annonce11);

        $annonce12 = new Annonce();
        $annonce12->setTitre('Chambre budget Montpellier Antigone');
        $annonce12->setDescription("Chambre de 14m² au quartier Antigone. Colocation calme, idéale pour étudiants.");
        $annonce12->setPrix(420.00); $annonce12->setLocalisation('Montpellier Antigone'); $annonce12->setStatut(Annonce::STATUT_DISPONIBLE); $annonce12->setColocation($colocation7); $annonce12->setMetaDescription('Chambre 14m² colocation Montpellier Antigone, 420€/mois.'); $manager->persist($annonce12);

        $annonce13 = new Annonce();
        $annonce13->setTitre('Chambre quartier cathédrale Strasbourg');
        $annonce13->setDescription("Chambre de 13m² à deux pas de la cathédrale de Strasbourg. Colocation internationale, ambiance chaleureuse.");
        $annonce13->setPrix(400.00); $annonce13->setLocalisation('Strasbourg centre'); $annonce13->setStatut(Annonce::STATUT_DISPONIBLE); $annonce13->setColocation($colocation8); $annonce13->setMetaDescription('Chambre 13m² colocation Strasbourg, 400€/mois.'); $manager->persist($annonce13);

        $annonce14 = new Annonce();
        $annonce14->setTitre('Chambre spacieuse Strasbourg Krutenau');
        $annonce14->setDescription("Chambre de 15m² dans le quartier animé de la Krutenau. Proche tramway, nombreux bars et restaurants.");
        $annonce14->setPrix(440.00); $annonce14->setLocalisation('Strasbourg Krutenau'); $annonce14->setStatut(Annonce::STATUT_DISPONIBLE); $annonce14->setColocation($colocation8); $annonce14->setMetaDescription('Chambre 15m² Strasbourg Krutenau, 440€/mois.'); $manager->persist($annonce14);

        $annonce15 = new Annonce();
        $annonce15->setTitre('Chambre étudiante Rennes Beaulieu');
        $annonce15->setDescription("Chambre de 14m² près du campus de Beaulieu. Colocation de 2 étudiants, wifi fibre, vélo à disposition.");
        $annonce15->setPrix(395.00); $annonce15->setLocalisation('Rennes Beaulieu'); $annonce15->setStatut(Annonce::STATUT_DISPONIBLE); $annonce15->setColocation($colocation9); $annonce15->setMetaDescription('Chambre 14m² colocation Rennes campus, 395€/mois.'); $manager->persist($annonce15);

        $annonce16 = new Annonce();
        $annonce16->setTitre('Petite chambre pas chère Rennes');
        $annonce16->setDescription("Chambre de 12m² idéale pour un budget serré. Colocation conviviale, proche métro Villejean-Université.");
        $annonce16->setPrix(360.00); $annonce16->setLocalisation('Rennes Villejean'); $annonce16->setStatut(Annonce::STATUT_DISPONIBLE); $annonce16->setColocation($colocation9); $annonce16->setMetaDescription('Chambre 12m² colocation Rennes pas chère, 360€/mois.'); $manager->persist($annonce16);

        // Photos nouvelles annonces
        $newPhotosData = [
            [$annonce9,  'annonce9_nantes.jpg',         'Nantes',       'Chambre cosy',       '#1abc9c'],
            [$annonce10, 'annonce10_nantes2.jpg',        'Nantes',       'Grande chambre',     '#3498db'],
            [$annonce11, 'annonce11_montpellier.jpg',    'Montpellier',  'Ensoleillée',        '#f39c12'],
            [$annonce12, 'annonce12_montpellier2.jpg',   'Montpellier',  'Budget',             '#e74c3c'],
            [$annonce13, 'annonce13_strasbourg.jpg',     'Strasbourg',   'Cathédrale',         '#9b59b6'],
            [$annonce14, 'annonce14_strasbourg2.jpg',    'Strasbourg',   'Krutenau',           '#2c3e50'],
            [$annonce15, 'annonce15_rennes.jpg',         'Rennes',       'Campus Beaulieu',    '#27ae60'],
            [$annonce16, 'annonce16_rennes2.jpg',        'Rennes',       'Villejean',          '#8e44ad'],
        ];
        foreach ($newPhotosData as [$annonce, $filename, $city, $label, $hex]) {
            $this->generatePlaceholderImage($uploadsDir, $filename, $city, $label, $hex);
            $photo = new PhotoAnnonce();
            $photo->setFilename($filename);
            $photo->setAlt($city . ' - ' . $label);
            $photo->setPosition(0);
            $photo->setAnnonce($annonce);
            $manager->persist($photo);
        }

        // Charges
        $charge1 = new Charge();
        $charge1->setType(Charge::TYPE_ELECTRICITE);
        $charge1->setDescription('Facture EDF ' . $moisFr[(int)date('n') - 1] . ' ' . date('Y'));
        $charge1->setMontant(120.00);
        $charge1->setDate(new \DateTimeImmutable('-10 days'));
        $charge1->setAnnee((int) date('Y'));
        $charge1->setMois(date('m'));
        $charge1->setColocation($colocation);
        $manager->persist($charge1);

        $charge2 = new Charge();
        $charge2->setType(Charge::TYPE_INTERNET);
        $charge2->setDescription('Abonnement internet');
        $charge2->setMontant(35.00);
        $charge2->setDate(new \DateTimeImmutable('-5 days'));
        $charge2->setAnnee((int) date('Y'));
        $charge2->setMois(date('m'));
        $charge2->setColocation($colocation);
        $manager->persist($charge2);

        // Tantièmes pour les charges (chambres passées explicitement pour éviter le problème de collection inverse)
        $chambresCol1 = [$chambre1, $chambre2, $chambre3];
        $surfaceTotaleCol1 = 18.5 + 14.0 + 20.0;
        foreach ([$charge1, $charge2] as $charge) {
            foreach ($chambresCol1 as $ch) {
                $pct     = ((float) $ch->getSurface() / $surfaceTotaleCol1) * 100;
                $montant = ((float) $charge->getMontant() * $pct) / 100;
                $t = new Tantieme();
                $t->setChambre($ch);
                $t->setCharge($charge);
                $t->setPourcentage((string) round($pct, 2));
                $t->setMontantDu((string) round($montant, 2));
                $manager->persist($t);
            }
        }

        // Evaluations propriétaire
        $evalPro1 = new EvaluationProprietaire();
        $evalPro1->setLocataire($locataire);
        $evalPro1->setProprietaire($proprio);
        $evalPro1->setColocation($colocation);
        $evalPro1->setNote(5);
        $evalPro1->setCommentaire('Propriétaire très réactif, toujours disponible en cas de problème. Je recommande.');
        $manager->persist($evalPro1);

        $evalPro2 = new EvaluationProprietaire();
        $evalPro2->setLocataire($locataire2);
        $evalPro2->setProprietaire($proprio);
        $evalPro2->setColocation($colocation);
        $evalPro2->setNote(4);
        $evalPro2->setCommentaire('Bon propriétaire, logement bien entretenu. Quelques délais de réponse mais globalement satisfait.');
        $manager->persist($evalPro2);

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

        // Message initial pour que Marie ait une conversation
        $msg = new Message();
        $msg->setExpediteur($locataire);
        $msg->setDestinataire($proprio);
        $msg->setColocation($colocation);
        $msg->setContenu('Bonjour, je suis intéressée par votre colocation.');
        $manager->persist($msg);

        $manager->flush();
    }
}
