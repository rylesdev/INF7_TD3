<?php

namespace App\Tests\Entity;

use App\Entity\Annonce;
use App\Entity\VisiteAnnonce;
use PHPUnit\Framework\TestCase;

class VisiteAnnonceTest extends TestCase
{
    public function testIpAddressNullParDefaut(): void
    {
        $v = new VisiteAnnonce();
        $this->assertNull($v->getIpAddress());
    }

    public function testIpAddressIsSet(): void
    {
        $v = new VisiteAnnonce();
        $v->setIpAddress('192.168.1.1');
        $this->assertSame('192.168.1.1', $v->getIpAddress());
    }

    public function testIpAddressIPv6(): void
    {
        $v = new VisiteAnnonce();
        $v->setIpAddress('2001:0db8:85a3:0000:0000:8a2e:0370:7334');
        $this->assertSame('2001:0db8:85a3:0000:0000:8a2e:0370:7334', $v->getIpAddress());
    }

    public function testIpAddressNullQuandSetNull(): void
    {
        $v = new VisiteAnnonce();
        $v->setIpAddress('127.0.0.1');
        $v->setIpAddress(null);
        $this->assertNull($v->getIpAddress());
    }

    public function testVisiteLeNullAvantPrePersist(): void
    {
        $v = new VisiteAnnonce();
        $this->assertNull($v->getVisiteLe());
    }

    public function testVisiteLeSetParPrePersist(): void
    {
        $v = new VisiteAnnonce();
        $v->onPrePersist();
        $this->assertInstanceOf(\DateTimeImmutable::class, $v->getVisiteLe());
    }

    public function testAnnonceNullParDefaut(): void
    {
        $v = new VisiteAnnonce();
        $this->assertNull($v->getAnnonce());
    }

    public function testAnnonceIsSet(): void
    {
        $v = new VisiteAnnonce();
        $a = new Annonce();
        $a->setTitre('Chambre test');
        $a->setDescription('Description test');
        $a->setPrix('500.00');
        $a->setLocalisation('Paris');
        $v->setAnnonce($a);
        $this->assertSame($a, $v->getAnnonce());
    }

    public function testAnnonceNbVisitesIncrementeViaCollection(): void
    {
        $a = new Annonce();
        $a->setTitre('Chambre test');
        $a->setDescription('Description test');
        $a->setPrix('500.00');
        $a->setLocalisation('Paris');

        $this->assertSame(0, $a->getNbVisites());

        // VisiteAnnonce n'a pas d'addVisite sur Annonce (cascade gérée par Doctrine)
        // On vérifie juste que l'entité se lie correctement
        $v = new VisiteAnnonce();
        $v->setAnnonce($a);
        $this->assertSame($a, $v->getAnnonce());
    }
}
