<?php

namespace App\Tests\Controller;

use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('fixtures')]
class OwnerAccessTest extends WebTestCase
{
    private function loginAsProprio(): KernelBrowser
    {
        $client = static::createClient();
        $user   = static::getContainer()->get(UserRepository::class)
            ->findOneBy(['email' => 'proprio@colocation.com']);
        $client->loginUser($user);
        return $client;
    }

    private function loginAsLocataire(): KernelBrowser
    {
        $client = static::createClient();
        $user   = static::getContainer()->get(UserRepository::class)
            ->findOneBy(['email' => 'locataire@colocation.com']);
        $client->loginUser($user);
        return $client;
    }

    // ── Accès propriétaire ────────────────────────────────────────────────────

    public function testDashboardAccessible(): void
    {
        $client = $this->loginAsProprio();
        $client->request('GET', '/proprietaire');
        $this->assertResponseIsSuccessful();
    }

    public function testDashboardAfficheCardsStats(): void
    {
        $client = $this->loginAsProprio();
        $client->request('GET', '/proprietaire');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.display-6');
    }

    public function testColocationsAccessible(): void
    {
        $client = $this->loginAsProprio();
        $client->request('GET', '/proprietaire/colocations');
        $this->assertResponseIsSuccessful();
    }

    public function testColocationsAfficheLesFixtures(): void
    {
        $client = $this->loginAsProprio();
        $client->request('GET', '/proprietaire/colocations');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Les Lilas');
        $this->assertSelectorTextContains('body', 'Villa Soleil');
    }

    public function testAnnoncesAccessible(): void
    {
        $client = $this->loginAsProprio();
        $client->request('GET', '/proprietaire/annonces');
        $this->assertResponseIsSuccessful();
    }

    public function testLoyersAccessible(): void
    {
        $client = $this->loginAsProprio();
        $client->request('GET', '/proprietaire/loyers');
        $this->assertResponseIsSuccessful();
    }

    public function testChargesAccessible(): void
    {
        $client = $this->loginAsProprio();
        $client->request('GET', '/proprietaire/charges');
        $this->assertResponseIsSuccessful();
    }

    public function testMessagerieAccessible(): void
    {
        $client = $this->loginAsProprio();
        $client->request('GET', '/proprietaire/messagerie');
        $this->assertResponseIsSuccessful();
    }

    public function testEvaluationsAccessible(): void
    {
        $client = $this->loginAsProprio();
        $client->request('GET', '/proprietaire/evaluations');
        $this->assertResponseIsSuccessful();
    }

    public function testFormulaireNouvelleColocationAccessible(): void
    {
        $client = $this->loginAsProprio();
        $client->request('GET', '/proprietaire/colocations/new');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="colocation[nom]"]');
        $this->assertSelectorExists('input[name="colocation[ville]"]');
    }

    public function testCreerColocationValideRedirige(): void
    {
        $client = $this->loginAsProprio();
        $client->request('GET', '/proprietaire/colocations/new');
        $client->submitForm('Enregistrer', [
            'colocation[nom]'        => 'Colocation Test PHPUnit',
            'colocation[adresse]'    => '42 rue de la Paix',
            'colocation[ville]'      => 'Paris',
            'colocation[codePostal]' => '75001',
        ]);
        $this->assertResponseRedirects('/proprietaire/colocations');
    }

    public function testColocationCreeeApparaîtDansLaListe(): void
    {
        $client = $this->loginAsProprio();
        $client->request('GET', '/proprietaire/colocations/new');
        $client->submitForm('Enregistrer', [
            'colocation[nom]'        => 'Colocation PHPUnit Visible',
            'colocation[adresse]'    => '1 avenue du Test',
            'colocation[ville]'      => 'Lyon',
            'colocation[codePostal]' => '69001',
        ]);
        $client->followRedirect();
        $this->assertSelectorTextContains('body', 'Colocation PHPUnit Visible');
    }

    public function testStatsApiRetourneJson(): void
    {
        $client = $this->loginAsProprio();
        $client->request('GET', '/proprietaire/api/stats');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/json');
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('revenus', $data);
        $this->assertCount(12, $data['labels']);
    }

    // ── Contrôle d'accès : locataire interdit sur routes propriétaire ─────────

    public function testLocataireInterditSurDashboard(): void
    {
        $client = $this->loginAsLocataire();
        $client->request('GET', '/proprietaire');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testLocataireInterditSurColocations(): void
    {
        $client = $this->loginAsLocataire();
        $client->request('GET', '/proprietaire/colocations');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testLocataireInterditSurAnnonces(): void
    {
        $client = $this->loginAsLocataire();
        $client->request('GET', '/proprietaire/annonces');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testLocataireInterditSurCreationColocation(): void
    {
        $client = $this->loginAsLocataire();
        $client->request('GET', '/proprietaire/colocations/new');
        $this->assertResponseStatusCodeSame(403);
    }
}
