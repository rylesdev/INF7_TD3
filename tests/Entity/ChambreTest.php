<?php

namespace App\Tests\Entity;

use App\Entity\Chambre;
use App\Entity\Colocation;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ChambreTest extends TestCase
{
    private function makeColocation(): Colocation
    {
        $c = new Colocation();
        $c->setNom('Coloc Test');
        $c->setAdresse('1 rue Test');
        $c->setVille('Lyon');
        $c->setCodePostal('69001');
        $c->setLoyer(1200.00);
        return $c;
    }

    private function makeChambre(): Chambre
    {
        $ch = new Chambre();
        $ch->setNom('Chambre 1');
        $ch->setSurface('18.5');
        return $ch;
    }

    public function testNomIsSet(): void
    {
        $ch = $this->makeChambre();
        $this->assertSame('Chambre 1', $ch->getNom());
    }

    public function testNomSanitizesHtml(): void
    {
        $ch = new Chambre();
        $ch->setNom('<b>Chambre</b> 2');
        $this->assertStringNotContainsString('<b>', $ch->getNom());
        $this->assertStringContainsString('Chambre', $ch->getNom());
    }

    public function testSurfaceIsSet(): void
    {
        $ch = $this->makeChambre();
        $this->assertSame('18.5', $ch->getSurface());
    }

    public function testLoyerMensuelNullParDefaut(): void
    {
        $ch = new Chambre();
        $this->assertNull($ch->getLoyerMensuel());
    }

    public function testLoyerMensuelIsSet(): void
    {
        $ch = $this->makeChambre();
        $ch->setLoyerMensuel('600.00');
        $this->assertSame('600.00', $ch->getLoyerMensuel());
    }

    public function testLocataireNullParDefaut(): void
    {
        $ch = new Chambre();
        $this->assertNull($ch->getLocataire());
    }

    public function testLocataireIsSet(): void
    {
        $ch = $this->makeChambre();
        $u = new User();
        $u->setPrenom('Marie');
        $u->setNom('Martin');
        $ch->setLocataire($u);
        $this->assertSame($u, $ch->getLocataire());
    }

    public function testColocationIsSet(): void
    {
        $ch = $this->makeChambre();
        $c = $this->makeColocation();
        $ch->setColocation($c);
        $this->assertSame($c, $ch->getColocation());
    }

    public function testTantièmesCollectionStartsEmpty(): void
    {
        $ch = new Chambre();
        $this->assertCount(0, $ch->getTantiemes());
    }

    public function testLoyersCollectionStartsEmpty(): void
    {
        $ch = new Chambre();
        $this->assertCount(0, $ch->getLoyers());
    }

    public function testPourcentageSurfaceSansColocation(): void
    {
        $ch = new Chambre();
        $ch->setSurface('18.5');
        $this->assertSame(0.0, $ch->getPourcentageSurface());
    }

    public function testPourcentageSurfaceAvecColocation(): void
    {
        $c = $this->makeColocation();

        $ch1 = new Chambre();
        $ch1->setNom('Ch1');
        $ch1->setSurface('18.5');
        $c->addChambre($ch1);

        $ch2 = new Chambre();
        $ch2->setNom('Ch2');
        $ch2->setSurface('14.0');
        $c->addChambre($ch2);

        // 18.5 / (18.5 + 14.0) * 100 = 56.92%
        $this->assertEqualsWithDelta(56.92, $ch1->getPourcentageSurface(), 0.01);
    }

    public function testPourcentageSurfaceAvecUneChambre(): void
    {
        $c = $this->makeColocation();

        $ch = new Chambre();
        $ch->setNom('Ch1');
        $ch->setSurface('20.0');
        $c->addChambre($ch);

        $this->assertSame(100.0, $ch->getPourcentageSurface());
    }
}
