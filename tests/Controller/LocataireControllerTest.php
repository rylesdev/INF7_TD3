<?php

namespace App\Tests\Controller;

use App\Repository\QuittanceRepository;
use App\Repository\UserRepository;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[Group('fixtures')]
class LocataireControllerTest extends WebTestCase
{
    private function loginAsLocataire(): KernelBrowser
    {
        $client = static::createClient();
        $user   = static::getContainer()->get(UserRepository::class)
            ->findOneBy(['email' => 'locataire@colocation.com']);
        $client->loginUser($user);
        return $client;
    }

    private function loginAsProprio(): KernelBrowser
    {
        $client = static::createClient();
        $user   = static::getContainer()->get(UserRepository::class)
            ->findOneBy(['email' => 'proprio@colocation.com']);
        $client->loginUser($user);
        return $client;
    }

    public function testDashboardAccessible(): void
    {
        $client = $this->loginAsLocataire();
        $client->request('GET', '/locataire');
        $this->assertResponseIsSuccessful();
    }

    public function testDashboardAfficheNomLocataire(): void
    {
        $client = $this->loginAsLocataire();
        $client->request('GET', '/locataire');
        $this->assertResponseIsSuccessful();
        // La fixture nomme cet utilisateur "Marie"
        $this->assertSelectorTextContains('body', 'Marie');
    }

    public function testDashboardAfficheCarteOuSection(): void
    {
        $client = $this->loginAsLocataire();
        $client->request('GET', '/locataire');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.card');
    }

    public function testLoyersAccessible(): void
    {
        $client = $this->loginAsLocataire();
        $client->request('GET', '/locataire/loyers');
        $this->assertResponseIsSuccessful();
    }

    public function testLoyersAfficheLoyerFixture(): void
    {
        $client = $this->loginAsLocataire();
        $client->request('GET', '/locataire/loyers');
        $this->assertResponseIsSuccessful();
        // Un loyer de 650€ est associé à Marie dans les fixtures
        $this->assertSelectorTextContains('body', '650');
    }

    public function testQuittancesAccessible(): void
    {
        $client = $this->loginAsLocataire();
        $client->request('GET', '/locataire/quittances');
        $this->assertResponseIsSuccessful();
    }

    public function testTantièmesAccessible(): void
    {
        $client = $this->loginAsLocataire();
        $client->request('GET', '/locataire/tantiemes');
        $this->assertResponseIsSuccessful();
    }

    public function testMessagerieAccessible(): void
    {
        // Marie est assignée à Chambre A → pas de redirect "aucune chambre"
        $client = $this->loginAsLocataire();
        $client->request('GET', '/locataire/messagerie');
        $this->assertResponseIsSuccessful();
    }

    public function testMessagerieAfficheFormulaireEnvoi(): void
    {
        $client = $this->loginAsLocataire();
        $client->request('GET', '/locataire/messagerie');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('textarea[name="message[contenu]"]');
    }

    public function testEnvoyerMessageRedirigeVersMessagerie(): void
    {
        $client = $this->loginAsLocataire();
        $crawler = $client->request('GET', '/locataire/messagerie');
        // Le bouton n'a pas de texte quand des messages existent (juste une icône)
        $form = $crawler->filter('button[type="submit"]')->first()->form([
            'message[contenu]' => 'Message de test automatisé PHPUnit.',
        ]);
        $client->submit($form);
        // La redirection inclut ?with=<userId>, on vérifie juste que c'est un redirect
        $this->assertResponseRedirects();
    }

    public function testNotificationsAccessible(): void
    {
        $client = $this->loginAsLocataire();
        $client->request('GET', '/locataire/notifications');
        $this->assertResponseIsSuccessful();
    }

    public function testEvaluationsAccessible(): void
    {
        $client = $this->loginAsLocataire();
        $client->request('GET', '/locataire/evaluations');
        $this->assertResponseIsSuccessful();
    }

    public function testProprietaireAccedeDashboardLocataireViaHierarchie(): void
    {
        // ROLE_PROPRIETAIRE hérite de ROLE_USER → accès aux routes locataire autorisé
        $client = $this->loginAsProprio();
        $client->request('GET', '/locataire');
        $this->assertResponseIsSuccessful();
    }

    public function testLocataireInterditSurDashboardProprietaire(): void
    {
        $client = $this->loginAsLocataire();
        $client->request('GET', '/proprietaire');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testLocataireInterditSurLoyersProprietaire(): void
    {
        $client = $this->loginAsLocataire();
        $client->request('GET', '/proprietaire/loyers');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testTelechargementQuittancePdfGenereUnPdf(): void
    {
        $client = $this->loginAsLocataire();

        // Récupère la première quittance disponible en BDD (générée par les fixtures via paiement loyer)
        $quittance = static::getContainer()->get(QuittanceRepository::class)->findAll();
        if (empty($quittance)) {
            $this->markTestSkipped('Aucune quittance en BDD - passer le loyer en "payé" d\'abord.');
        }

        $id = $quittance[0]->getId();
        $client->request('GET', '/locataire/quittances/' . $id . '/pdf');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment', $client->getResponse()->headers->get('Content-Disposition'));
        $this->assertGreaterThan(1000, strlen($client->getResponse()->getContent()), 'Le PDF doit peser au moins 1 ko');
    }
}
