<?php

namespace App\Tests\Entity;

use App\Entity\Colocation;
use App\Entity\EvaluationLocataire;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class EvaluationLocataireTest extends TestCase
{
    private function makeEvaluation(): EvaluationLocataire
    {
        $e = new EvaluationLocataire();

        $locataire = new User();
        $locataire->setPrenom('Alice');
        $locataire->setNom('Martin');

        $proprietaire = new User();
        $proprietaire->setPrenom('Bob');
        $proprietaire->setNom('Durand');

        $colocation = new Colocation();
        $colocation->setNom('Coloc Test');
        $colocation->setAdresse('5 rue de la Paix');
        $colocation->setVille('Paris');
        $colocation->setCodePostal('75001');
        $colocation->setLoyer(1500.00);

        $e->setLocataire($locataire);
        $e->setProprietaire($proprietaire);
        $e->setColocation($colocation);

        return $e;
    }

    public function testNoteParDefautCinq(): void
    {
        $e = new EvaluationLocataire();
        $this->assertSame(5, $e->getNote());
    }

    public function testNoteIsSet(): void
    {
        $e = $this->makeEvaluation();
        $e->setNote(3);
        $this->assertSame(3, $e->getNote());
    }

    public function testNoteEstPlafonneeAMax(): void
    {
        $e = $this->makeEvaluation();
        $e->setNote(10);
        $this->assertSame(5, $e->getNote());
    }

    public function testNoteEstPlancheeAMin(): void
    {
        $e = $this->makeEvaluation();
        $e->setNote(0);
        $this->assertSame(1, $e->getNote());
    }

    public function testNoteNegativeEstClampee(): void
    {
        $e = $this->makeEvaluation();
        $e->setNote(-3);
        $this->assertSame(1, $e->getNote());
    }

    public function testCommentaireNullParDefaut(): void
    {
        $e = new EvaluationLocataire();
        $this->assertNull($e->getCommentaire());
    }

    public function testCommentaireIsSet(): void
    {
        $e = $this->makeEvaluation();
        $e->setCommentaire('Très bon locataire, toujours à l\'heure.');
        $this->assertSame("Très bon locataire, toujours à l'heure.", $e->getCommentaire());
    }

    public function testCommentaireStripsHtmlTags(): void
    {
        $e = $this->makeEvaluation();
        $e->setCommentaire('<b>Excellent</b> locataire.');
        $this->assertStringNotContainsString('<b>', $e->getCommentaire());
        $this->assertStringContainsString('Excellent', $e->getCommentaire());
    }

    public function testCommentaireNullQuandSetToNull(): void
    {
        $e = $this->makeEvaluation();
        $e->setCommentaire(null);
        $this->assertNull($e->getCommentaire());
    }

    public function testLocataireIsSet(): void
    {
        $e = $this->makeEvaluation();
        $this->assertInstanceOf(User::class, $e->getLocataire());
        $this->assertSame('Alice', $e->getLocataire()->getPrenom());
    }

    public function testProprietaireIsSet(): void
    {
        $e = $this->makeEvaluation();
        $this->assertInstanceOf(User::class, $e->getProprietaire());
        $this->assertSame('Bob', $e->getProprietaire()->getPrenom());
    }

    public function testColocationIsSet(): void
    {
        $e = $this->makeEvaluation();
        $this->assertInstanceOf(Colocation::class, $e->getColocation());
    }

    public function testCreeLcNullAvantPrePersist(): void
    {
        $e = new EvaluationLocataire();
        $this->assertNull($e->getCreeLe());
    }

    public function testCreeLcSetParPrePersist(): void
    {
        $e = new EvaluationLocataire();
        $e->onPrePersist();
        $this->assertInstanceOf(\DateTimeImmutable::class, $e->getCreeLe());
    }
}
