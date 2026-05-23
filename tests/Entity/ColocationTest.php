<?php

namespace App\Tests\Entity;

use App\Entity\Chambre;
use App\Entity\Colocation;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class ColocationTest extends TestCase
{
    private function makeColocation(): Colocation
    {
        $c = new Colocation();
        $c->setNom('Test');
        $c->setAdresse('1 rue Test');
        $c->setVille('Paris');
        $c->setCodePostal('75001');
        $c->setLoyer(1500.00);
        return $c;
    }

    public function testNomIsSet(): void
    {
        $c = $this->makeColocation();
        $this->assertSame('Test', $c->getNom());
    }

    public function testAdresseIsSet(): void
    {
        $c = $this->makeColocation();
        $this->assertSame('1 rue Test', $c->getAdresse());
    }

    public function testVilleIsSet(): void
    {
        $c = $this->makeColocation();
        $this->assertSame('Paris', $c->getVille());
    }

    public function testCodePostalIsSet(): void
    {
        $c = $this->makeColocation();
        $this->assertSame('75001', $c->getCodePostal());
    }

    public function testLoyerIsSet(): void
    {
        $c = $this->makeColocation();
        $this->assertSame(1500.00, $c->getLoyer());
    }

    public function testProprietaireAssignment(): void
    {
        $c = $this->makeColocation();
        $u = new User();
        $u->setPrenom('Jean');
        $u->setNom('Dupont');
        $c->setProprietaire($u);
        $this->assertSame($u, $c->getProprietaire());
    }

    public function testChambresCollectionStartsEmpty(): void
    {
        $c = $this->makeColocation();
        $this->assertCount(0, $c->getChambres());
    }

    public function testSurfaceTotaleWithNoChambres(): void
    {
        $c = $this->makeColocation();
        $this->assertSame(0.0, $c->getSurfaceTotale());
    }

    public function testSurfaceTotaleWithChambres(): void
    {
        $c = $this->makeColocation();

        $ch1 = new Chambre();
        $ch1->setSurface('18.5');
        $ch1->setColocation($c);

        $ch2 = new Chambre();
        $ch2->setSurface('14.0');
        $ch2->setColocation($c);

        $this->assertEqualsWithDelta(32.5, $c->getSurfaceTotale(), 0.001);
    }

    public function testLatitudeLongitudeNullByDefault(): void
    {
        $c = $this->makeColocation();
        $this->assertNull($c->getLatitude());
        $this->assertNull($c->getLongitude());
    }

    public function testLatitudeLongitudeCanBeSet(): void
    {
        $c = $this->makeColocation();
        $c->setLatitude(48.8566);
        $c->setLongitude(2.3522);
        $this->assertSame(48.8566, $c->getLatitude());
        $this->assertSame(2.3522, $c->getLongitude());
    }

    public function testCreatedAtSetAutomatically(): void
    {
        $c = $this->makeColocation();
        $this->assertInstanceOf(\DateTimeImmutable::class, $c->getCreatedAt());
    }

    public function testDescriptionCanBeNull(): void
    {
        $c = $this->makeColocation();
        $this->assertNull($c->getDescription());
    }

    public function testDescriptionIsSet(): void
    {
        $c = $this->makeColocation();
        $c->setDescription('Belle colocation.');
        $this->assertSame('Belle colocation.', $c->getDescription());
    }
}
