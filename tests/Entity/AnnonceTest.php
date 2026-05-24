<?php

namespace App\Tests\Entity;

use App\Entity\Annonce;
use App\Entity\Colocation;
use App\Entity\PhotoAnnonce;
use PHPUnit\Framework\TestCase;

class AnnonceTest extends TestCase
{
    private function makeAnnonce(): Annonce
    {
        $a = new Annonce();
        $a->setTitre('Chambre centre-ville');
        $a->setDescription('Belle chambre lumineuse en plein centre.');
        $a->setPrix('450.00');
        $a->setLocalisation('Paris 75001');
        return $a;
    }

    public function testTitreIsSet(): void
    {
        $a = $this->makeAnnonce();
        $this->assertSame('Chambre centre-ville', $a->getTitre());
    }

    public function testTitreSanitizesHtml(): void
    {
        $a = new Annonce();
        $a->setTitre('<script>xss</script>Chambre');
        $this->assertStringNotContainsString('<script>', $a->getTitre());
        $this->assertStringContainsString('Chambre', $a->getTitre());
    }

    public function testDescriptionIsSet(): void
    {
        $a = $this->makeAnnonce();
        $this->assertSame('Belle chambre lumineuse en plein centre.', $a->getDescription());
    }

    public function testPrixIsSet(): void
    {
        $a = $this->makeAnnonce();
        $this->assertSame('450.00', $a->getPrix());
    }

    public function testLocalisationIsSet(): void
    {
        $a = $this->makeAnnonce();
        $this->assertSame('Paris 75001', $a->getLocalisation());
    }

    public function testLocalisationSanitizesHtml(): void
    {
        $a = new Annonce();
        $a->setLocalisation('<b>Paris</b>');
        $this->assertStringNotContainsString('<b>', $a->getLocalisation());
        $this->assertStringContainsString('Paris', $a->getLocalisation());
    }

    public function testStatutParDefautDisponible(): void
    {
        $a = new Annonce();
        $this->assertSame(Annonce::STATUT_DISPONIBLE, $a->getStatut());
    }

    public function testIsDisponibleParDefaut(): void
    {
        $a = new Annonce();
        $this->assertTrue($a->isDisponible());
    }

    public function testIsNotDisponibleApresChangement(): void
    {
        $a = new Annonce();
        $a->setStatut(Annonce::STATUT_INDISPONIBLE);
        $this->assertFalse($a->isDisponible());
    }

    public function testStatutConstantes(): void
    {
        $this->assertSame('disponible', Annonce::STATUT_DISPONIBLE);
        $this->assertSame('indisponible', Annonce::STATUT_INDISPONIBLE);
    }

    public function testMetaDescriptionNullParDefaut(): void
    {
        $a = new Annonce();
        $this->assertNull($a->getMetaDescription());
    }

    public function testMetaDescriptionIsSet(): void
    {
        $a = $this->makeAnnonce();
        $a->setMetaDescription('Annonce SEO optimisée');
        $this->assertSame('Annonce SEO optimisée', $a->getMetaDescription());
    }

    public function testPhotosCollectionStartsEmpty(): void
    {
        $a = new Annonce();
        $this->assertCount(0, $a->getPhotos());
    }

    public function testVisitesCollectionStartsEmpty(): void
    {
        $a = new Annonce();
        $this->assertCount(0, $a->getVisites());
    }

    public function testNbVisitesIsZeroInitialement(): void
    {
        $a = new Annonce();
        $this->assertSame(0, $a->getNbVisites());
    }

    public function testGetPremierPhotoNullSansPhoto(): void
    {
        $a = new Annonce();
        $this->assertNull($a->getPremierPhoto());
    }

    public function testAddPhoto(): void
    {
        $a = new Annonce();
        $p = new PhotoAnnonce();
        $a->addPhoto($p);
        $this->assertCount(1, $a->getPhotos());
        $this->assertSame($a, $p->getAnnonce());
    }

    public function testAddPhotoNeDupliquePas(): void
    {
        $a = new Annonce();
        $p = new PhotoAnnonce();
        $a->addPhoto($p);
        $a->addPhoto($p);
        $this->assertCount(1, $a->getPhotos());
    }

    public function testRemovePhoto(): void
    {
        $a = new Annonce();
        $p = new PhotoAnnonce();
        $a->addPhoto($p);
        $a->removePhoto($p);
        $this->assertCount(0, $a->getPhotos());
    }

    public function testColocationIsSet(): void
    {
        $a = $this->makeAnnonce();
        $c = new Colocation();
        $a->setColocation($c);
        $this->assertSame($c, $a->getColocation());
    }

    public function testCreatedAtNullAvantPrePersist(): void
    {
        $a = new Annonce();
        $this->assertNull($a->getCreatedAt());
    }

    public function testCreatedAtSetParPrePersist(): void
    {
        $a = new Annonce();
        $a->onPrePersist();
        $this->assertInstanceOf(\DateTimeImmutable::class, $a->getCreatedAt());
    }

    public function testUpdatedAtNullParDefaut(): void
    {
        $a = new Annonce();
        $this->assertNull($a->getUpdatedAt());
    }

    public function testUpdatedAtSetParPreUpdate(): void
    {
        $a = new Annonce();
        $a->onPreUpdate();
        $this->assertInstanceOf(\DateTimeImmutable::class, $a->getUpdatedAt());
    }
}
