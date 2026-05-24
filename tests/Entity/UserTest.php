<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private function makeUser(): User
    {
        $u = new User();
        $u->setPrenom('Jean');
        $u->setNom('Dupont');
        $u->setEmail('jean.dupont@example.com');
        return $u;
    }

    public function testEmailIsSet(): void
    {
        $u = $this->makeUser();
        $this->assertSame('jean.dupont@example.com', $u->getEmail());
    }

    public function testEmailIsLowercasedAndTrimmed(): void
    {
        $u = new User();
        $u->setEmail('  TEST@MAIL.COM  ');
        $this->assertSame('test@mail.com', $u->getEmail());
    }

    public function testPrenomIsSet(): void
    {
        $u = $this->makeUser();
        $this->assertSame('Jean', $u->getPrenom());
    }

    public function testPrenomSanitizesHtml(): void
    {
        $u = new User();
        $u->setPrenom('<script>alert(1)</script>Jean');
        $this->assertStringNotContainsString('<script>', $u->getPrenom());
        $this->assertStringContainsString('Jean', $u->getPrenom());
    }

    public function testNomIsSet(): void
    {
        $u = $this->makeUser();
        $this->assertSame('Dupont', $u->getNom());
    }

    public function testNomSanitizesHtml(): void
    {
        $u = new User();
        $u->setNom('<b>Dupont</b>');
        $this->assertStringNotContainsString('<b>', $u->getNom());
        $this->assertStringContainsString('Dupont', $u->getNom());
    }

    public function testNomComplet(): void
    {
        $u = $this->makeUser();
        $this->assertSame('Jean Dupont', $u->getNomComplet());
    }

    public function testTelephoneNullByDefault(): void
    {
        $u = new User();
        $this->assertNull($u->getTelephone());
    }

    public function testTelephoneIsSet(): void
    {
        $u = $this->makeUser();
        $u->setTelephone('0612345678');
        $this->assertSame('0612345678', $u->getTelephone());
    }

    public function testTelephoneNullWhenSetToNull(): void
    {
        $u = $this->makeUser();
        $u->setTelephone('0612345678');
        $u->setTelephone(null);
        $this->assertNull($u->getTelephone());
    }

    public function testTelephoneStripsInvalidChars(): void
    {
        $u = new User();
        $u->setTelephone('+33 6-12-abc-345');
        // Le regex /[^0-9\+\-\s]/ retire les lettres, les tirets restent
        $this->assertSame('+33 6-12--345', $u->getTelephone());
    }

    public function testRolesAlwaysContainRoleUser(): void
    {
        $u = new User();
        $this->assertContains('ROLE_USER', $u->getRoles());
    }

    public function testRolesAreUnique(): void
    {
        $u = new User();
        $u->setRoles(['ROLE_USER']);
        $roles = $u->getRoles();
        $this->assertSame($roles, array_unique($roles));
    }

    public function testIsProprietaire(): void
    {
        $u = new User();
        $u->setRoles(['ROLE_PROPRIETAIRE']);
        $this->assertTrue($u->isProprietaire());
    }

    public function testIsNotProprietaireBySDefault(): void
    {
        $u = new User();
        $this->assertFalse($u->isProprietaire());
    }

    public function testGetUserIdentifier(): void
    {
        $u = $this->makeUser();
        $this->assertSame('jean.dupont@example.com', $u->getUserIdentifier());
    }

    public function testPhotoProfilNullByDefault(): void
    {
        $u = new User();
        $this->assertNull($u->getPhotoProfil());
    }

    public function testPhotoProfilIsSet(): void
    {
        $u = new User();
        $u->setPhotoProfil('avatar.jpg');
        $this->assertSame('avatar.jpg', $u->getPhotoProfil());
    }

    public function testCreatedAtNullBeforePrePersist(): void
    {
        $u = new User();
        $this->assertNull($u->getCreatedAt());
    }

    public function testCreatedAtSetByPrePersist(): void
    {
        $u = new User();
        $u->onPrePersist();
        $this->assertInstanceOf(\DateTimeImmutable::class, $u->getCreatedAt());
    }

    public function testCollectionsStartEmpty(): void
    {
        $u = new User();
        $this->assertCount(0, $u->getColocations());
        $this->assertCount(0, $u->getChambres());
        $this->assertCount(0, $u->getMessagesEnvoyes());
        $this->assertCount(0, $u->getMessagesRecus());
        $this->assertCount(0, $u->getTaches());
        $this->assertCount(0, $u->getNotifications());
        $this->assertCount(0, $u->getEvaluations());
    }
}
