<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests d'intégration pour l'authentification JWT.
 * Nécessite la base de données colocation_db avec les fixtures chargées.
 * Comptes de test : proprio@colocation.com / Proprio1234!
 *                   locataire@colocation.com / Locataire1234!
 */
class JwtAuthTest extends WebTestCase
{
    public function testEndpointLoginExisteEtRepondEnJSON(): void
    {
        $client = static::createClient();
        // JSON vide : email manquant → 400 Bad Request (l'endpoint existe bien)
        $client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json'], '{}');

        $this->assertResponseStatusCodeSame(400);
        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testLoginJsonVideRetourne400(): void
    {
        $client = static::createClient();
        // Tableau vide → JSON "[]" : champ email absent → 400
        $client->jsonRequest('POST', '/api/login', []);

        $this->assertResponseStatusCodeSame(400);
    }

    public function testLoginAvecCredentialsInvalidesRetourne401(): void
    {
        $client = static::createClient();
        $client->jsonRequest('POST', '/api/login', [
            'email'    => 'inconnu@example.com',
            'password' => 'mauvaismdp',
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testApiAnnoncesSansTokenEstPublique(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/annonces');

        // GET /api/annonces est PUBLIC_ACCESS selon security.yaml
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
    }

    public function testApiProtegeSansTokenRetourne401(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/users');

        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * Test complet login → token → appel protégé.
     * Ne s'exécute que si les fixtures sont chargées (groupe "fixtures").
     *
     * @group fixtures
     */
    public function testLoginAvecCompteProprietaireRetourneToken(): void
    {
        $client = static::createClient();
        $client->jsonRequest('POST', '/api/login', [
            'email'    => 'proprio@colocation.com',
            'password' => 'Proprio1234!',
        ]);

        $this->assertResponseIsSuccessful();

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data, 'La réponse doit contenir un token JWT');
        $this->assertNotEmpty($data['token']);
    }

    /**
     * @group fixtures
     */
    public function testTokenPermetAccesRouteProtegee(): void
    {
        $client = static::createClient();

        // 1. Authentification
        $client->jsonRequest('POST', '/api/login', [
            'email'    => 'locataire@colocation.com',
            'password' => 'Locataire1234!',
        ]);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $token = $data['token'];

        // 2. Appel d'une route protégée avec le token
        $client->request('GET', '/api/users', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        $this->assertResponseIsSuccessful();
    }
}
