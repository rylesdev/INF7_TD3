<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Vérifie que les routes protégées redirigent vers /login pour un visiteur anonyme.
 * Le pare-feu Symfony intercepte avant toute requête Doctrine.
 */
class ProtectedRoutesTest extends WebTestCase
{
    #[DataProvider('provideRoutesLocataire')]
    public function testRoutesLocataireRequierentConnexion(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseRedirects('/login', null, "La route $url devrait rediriger vers /login");
    }

    /** @return iterable<string, array{string}> */
    public static function provideRoutesLocataire(): iterable
    {
        yield 'dashboard locataire'  => ['/locataire'];
        yield 'loyers locataire'     => ['/locataire/loyers'];
        yield 'quittances locataire' => ['/locataire/quittances'];
        yield 'tantiemes locataire'  => ['/locataire/tantiemes'];
        yield 'messagerie locataire' => ['/locataire/messagerie'];
        yield 'notifications'        => ['/locataire/notifications'];
    }

    #[DataProvider('provideRoutesProprietaire')]
    public function testRoutesProprietaireRequierentConnexion(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseRedirects('/login', null, "La route $url devrait rediriger vers /login");
    }

    /** @return iterable<string, array{string}> */
    public static function provideRoutesProprietaire(): iterable
    {
        yield 'dashboard proprietaire' => ['/proprietaire'];
        yield 'annonces proprietaire'  => ['/proprietaire/annonces'];
        yield 'colocations'            => ['/proprietaire/colocations'];
        yield 'loyers proprietaire'    => ['/proprietaire/loyers'];
        yield 'charges'                => ['/proprietaire/charges'];
    }

    public function testPageAccueilEstAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertTrue(
            $client->getResponse()->isSuccessful() || $client->getResponse()->isRedirect(),
            'La page d\'accueil doit être accessible publiquement'
        );
    }

    public function testPageAnnoncesEstPublique(): void
    {
        $client = static::createClient();
        $client->request('GET', '/annonces');

        $this->assertResponseIsSuccessful();
    }
}
