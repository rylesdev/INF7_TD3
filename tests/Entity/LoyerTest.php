<?php

namespace App\Tests\Entity;

use App\Entity\Chambre;
use App\Entity\Colocation;
use App\Entity\Loyer;
use PHPUnit\Framework\TestCase;

class LoyerTest extends TestCase
{
    private function makeLoyer(): Loyer
    {
        $l = new Loyer();
        $l->setMontant('600.00');
        $l->setDateEcheance(new \DateTimeImmutable('2025-06-01'));
        $l->setMois('juin');
        $l->setAnnee(2025);
        return $l;
    }

    public function testMontantIsSet(): void
    {
        $l = $this->makeLoyer();
        $this->assertSame('600.00', $l->getMontant());
    }

    public function testStatutParDefautImpaye(): void
    {
        $l = new Loyer();
        $this->assertSame(Loyer::STATUT_IMPAYE, $l->getStatut());
    }

    public function testIsPayeRetourneFalseParDefaut(): void
    {
        $l = new Loyer();
        $this->assertFalse($l->isPaye());
    }

    public function testSetStatutPaye(): void
    {
        $l = $this->makeLoyer();
        $l->setStatut(Loyer::STATUT_PAYE);
        $this->assertTrue($l->isPaye());
        $this->assertSame(Loyer::STATUT_PAYE, $l->getStatut());
    }

    public function testSetStatutEnRetard(): void
    {
        $l = $this->makeLoyer();
        $l->setStatut(Loyer::STATUT_EN_RETARD);
        $this->assertFalse($l->isPaye());
        $this->assertSame(Loyer::STATUT_EN_RETARD, $l->getStatut());
    }

    public function testConstantes(): void
    {
        $this->assertSame('payé', Loyer::STATUT_PAYE);
        $this->assertSame('impayé', Loyer::STATUT_IMPAYE);
        $this->assertSame('en_retard', Loyer::STATUT_EN_RETARD);
    }

    public function testDateEcheanceIsSet(): void
    {
        $date = new \DateTimeImmutable('2025-06-01');
        $l = new Loyer();
        $l->setDateEcheance($date);
        $this->assertSame($date, $l->getDateEcheance());
    }

    public function testDatePaiementNullParDefaut(): void
    {
        $l = new Loyer();
        $this->assertNull($l->getDatePaiement());
    }

    public function testDatePaiementIsSet(): void
    {
        $date = new \DateTimeImmutable('2025-06-05');
        $l = $this->makeLoyer();
        $l->setDatePaiement($date);
        $this->assertSame($date, $l->getDatePaiement());
    }

    public function testMoisIsSet(): void
    {
        $l = $this->makeLoyer();
        $this->assertSame('juin', $l->getMois());
    }

    public function testAnneeIsSet(): void
    {
        $l = $this->makeLoyer();
        $this->assertSame(2025, $l->getAnnee());
    }

    public function testCreatedAtNullAvantPrePersist(): void
    {
        $l = new Loyer();
        $this->assertNull($l->getCreatedAt());
    }

    public function testCreatedAtSetParPrePersist(): void
    {
        $l = new Loyer();
        $l->onPrePersist();
        $this->assertInstanceOf(\DateTimeImmutable::class, $l->getCreatedAt());
    }

    public function testColocationIsSet(): void
    {
        $l = $this->makeLoyer();
        $c = new Colocation();
        $l->setColocation($c);
        $this->assertSame($c, $l->getColocation());
    }

    public function testChambreNullParDefaut(): void
    {
        $l = new Loyer();
        $this->assertNull($l->getChambre());
    }

    public function testChambreIsSet(): void
    {
        $l = $this->makeLoyer();
        $ch = new Chambre();
        $l->setChambre($ch);
        $this->assertSame($ch, $l->getChambre());
    }

    public function testQuittanceNullParDefaut(): void
    {
        $l = new Loyer();
        $this->assertNull($l->getQuittance());
    }
}
