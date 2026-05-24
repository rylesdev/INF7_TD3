<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testPageLoginEstAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    public function testPageRegisterEstAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testPageLoginAfficheTitre(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertSelectorTextContains('h1, h2, .card-title', 'Connexion');
    }

    public function testLoginAvecCredentialsInvalidesRedirige(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $client->submitForm('Se connecter', [
            '_username' => 'mauvais@email.com',
            '_password' => 'mauvaismdp',
        ]);

        // Symfony redirige vers /login après echec
        $this->assertResponseRedirects('/login');
    }

    public function testLogoutEstAccessible(): void
    {
        $client = static::createClient();
        // GET /logout redirige même sans session
        $client->request('GET', '/logout');
        $this->assertResponseRedirects();
    }
}
