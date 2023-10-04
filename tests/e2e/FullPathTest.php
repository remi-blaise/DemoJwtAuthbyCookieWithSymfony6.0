<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FullPathTest extends WebTestCase
{
    public function testFullPath(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Welcome');

        // When i click profile being not connected then i get redirected to the login page

        $crawler = $client->click($crawler->selectLink('Vers le profil')->link());

        $this->assertResponseRedirects();
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Login');

        // Then I log in with an existing account

        $client->submit($crawler->selectButton('Connect')->form(), [
            'login_form[username]' => 'Cheri',
            'login_form[plainPassword]' => 'qwertyuiop',
        ]);
        $this->assertResponseRedirects();
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Bienvenu');

        // I get redirected to the Profile page

        $crawler = $client->request('GET', '/');
        $crawler = $client->request('GET', '/profile');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Cheri');

        // When I log out, if I try to go to the Profile page then i get redirected to the Login page

        $crawler = $client->request('GET', '/logout');
        $crawler = $client->request('GET', '/profile');
        $this->assertResponseRedirects();
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Login');
    }
}
