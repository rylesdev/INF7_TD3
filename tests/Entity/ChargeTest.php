<?php

namespace App\Tests\Entity;

use App\Entity\Charge;
use App\Entity\Colocation;
use PHPUnit\Framework\TestCase;

class ChargeTest extends TestCase
{
    private function makeCharge(): Charge
    {
        $c = new Charge();
        $c->setType(Charge::TYPE_EAU);
        $c->setMontant('45.00');
        $c->setDate(new \DateTimeImmutable('2025-05-01'));
        $c->setMois('mai');
        $c->setAnnee(2025);
        return $c;
    }

    public function testTypeIsSet(): void
    {
        $c = $this->makeCharge();
        $this->assertSame(Charge::TYPE_EAU, $c->getType());
    }

    public function testMontantIsSet(): void
    {
        $c = $this->makeCharge();
        $this->assertSame('45.00', $c->getMontant());
    }

    public function testDateIsSet(): void
    {
        $date = new \DateTimeImmutable('2025-05-01');
        $c = new Charge();
        $c->setDate($date);
        $this->assertSame($date, $c->getDate());
    }

    public function testMoisIsSet(): void
    {
        $c = $this->makeCharge();
        $this->assertSame('mai', $c->getMois());
    }

    public function testAnneeIsSet(): void
    {
        $c = $this->makeCharge();
        $this->assertSame(2025, $c->getAnnee());
    }

    public function testDescriptionNullParDefaut(): void
    {
        $c = new Charge();
        $this->assertNull($c->getDescription());
    }

    public function testDescriptionIsSet(): void
    {
        $c = $this->makeCharge();
        $c->setDescription('Facture EDF mai 2025');
        $this->assertSame('Facture EDF mai 2025', $c->getDescription());
    }

    public function testCreatedAtNullAvantPrePersist(): void
    {
        $c = new Charge();
        $this->assertNull($c->getCreatedAt());
    }

    public function testCreatedAtSetParPrePersist(): void
    {
        $c = new Charge();
        $c->onPrePersist();
        $this->assertInstanceOf(\DateTimeImmutable::class, $c->getCreatedAt());
    }

    public function testColocationIsSet(): void
    {
        $c = $this->makeCharge();
        $col = new Colocation();
        $c->setColocation($col);
        $this->assertSame($col, $c->getColocation());
    }

    public function testTantièmesCollectionStartsEmpty(): void
    {
        $c = new Charge();
        $this->assertCount(0, $c->getTantiemes());
    }

    public function testConstantes(): void
    {
        $this->assertSame('eau', Charge::TYPE_EAU);
        $this->assertSame('electricite', Charge::TYPE_ELECTRICITE);
        $this->assertSame('internet', Charge::TYPE_INTERNET);
        $this->assertSame('taxes', Charge::TYPE_TAXES);
        $this->assertSame('autre', Charge::TYPE_AUTRE);
    }

    public function testGetLibelleTypeEau(): void
    {
        $c = new Charge();
        $c->setType(Charge::TYPE_EAU);
        $this->assertSame('Eau', $c->getLibelleType());
    }

    public function testGetLibelleTypeElectricite(): void
    {
        $c = new Charge();
        $c->setType(Charge::TYPE_ELECTRICITE);
        $this->assertSame('Électricité', $c->getLibelleType());
    }

    public function testGetLibelleTypeInternet(): void
    {
        $c = new Charge();
        $c->setType(Charge::TYPE_INTERNET);
        $this->assertSame('Internet', $c->getLibelleType());
    }

    public function testGetLibelleTypeTaxes(): void
    {
        $c = new Charge();
        $c->setType(Charge::TYPE_TAXES);
        $this->assertSame('Taxes', $c->getLibelleType());
    }

    public function testGetLibelleTypeAutre(): void
    {
        $c = new Charge();
        $c->setType(Charge::TYPE_AUTRE);
        $this->assertSame('Autre', $c->getLibelleType());
    }

    public function testTousLesTypesDonnentUnLibelle(): void
    {
        $types = [
            Charge::TYPE_EAU,
            Charge::TYPE_ELECTRICITE,
            Charge::TYPE_INTERNET,
            Charge::TYPE_TAXES,
            Charge::TYPE_AUTRE,
        ];

        foreach ($types as $type) {
            $c = new Charge();
            $c->setType($type);
            $this->assertNotEmpty($c->getLibelleType(), "Libellé vide pour le type: $type");
        }
    }
}
