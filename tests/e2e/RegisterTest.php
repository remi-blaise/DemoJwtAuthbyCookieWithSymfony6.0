<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterTest extends WebTestCase
{
    private function submitRegistration($username, $password)
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Register');

        $client->submit($crawler->selectButton('Register')->form(), [
            'registration_form[username]' => $username,
            'registration_form[plainPassword]' => $password,
            'registration_form[agreeTerms]' => true,
        ]);

        return $client;
    }

    public function testRegisterNewAccount(): void
    {
        $client = $this->submitRegistration('Alice' . random_int(0, 10000000000000000), 'qwertyuiop');
        $this->assertResponseRedirects();
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Bienvenue');
    }

    public function testRegisterExistingAccount(): void
    {
        $client = $this->submitRegistration('Cheri', 'qwertyuiop');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Register');
        $this->assertSelectorTextContains('body', 'There is already an account with this username');
    }

    public function testRegisterTooSmallPassword(): void
    {
        $client = $this->submitRegistration('Alice' . random_int(0, 10000000000000000), 'qwe');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Register');
        $this->assertSelectorTextContains('body', 'Your password should be at least 6 characters');
    }

    public function testLogin(): void
    {
        $username = 'Alice' . random_int(0, 10000000000000000);
        $password = 'qwertyuiop';
        $client = $this->submitRegistration($username, $password);

        $crawler = $client->request('GET', '/logout');
        $crawler = $client->followRedirect();

        $crawler = $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Login');
        $client->submit($crawler->selectButton('Connect')->form(), [
            'login_form[username]' => $username,
            'login_form[plainPassword]' => $password,
        ]);
        $this->assertResponseRedirects();
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', $username);
    }

    public function testLoginWithWrongPassword(): void
    {
        $username = 'Alice' . random_int(0, 10000000000000000);
        $password = 'wrong';
        $client = $this->submitRegistration($username, 'qwertyuiop');
        $crawler = $client->request('GET', '/logout');
        $crawler = $client->followRedirect();
        $crawler = $client->request('GET', '/login');
        $client->submit($crawler->selectButton('Connect')->form(), [
            'login_form[username]' => $username,
            'login_form[plainPassword]' => $password,
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Login');
        $this->assertSelectorTextContains('body', 'Wrong credential');

        $crawler = $client->request('GET', '/profile');
        $this->assertResponseRedirects();
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Login');
    }
}
