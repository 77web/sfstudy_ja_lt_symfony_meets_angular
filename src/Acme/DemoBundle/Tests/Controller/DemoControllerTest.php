<?php

namespace Acme\DemoBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DemoControllerTest extends WebTestCase
{
    public function testHelloWorld()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/demo/hello/Fabien');

        $this->assertEquals(1, $crawler->filter('html:contains("Hello {{ name }}")')->count());
        $this->assertEquals(1, $crawler->filter('script:contains("Fabien")')->count());
    }

    public function testRestApi()
    {
        $client = static::createClient();
        $client->request('GET', '/demo/rest/users.json');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $users = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(5, count($users));
        $this->assertArrayHasKey('id', $users[0]);
        $this->assertArrayHasKey('name', $users[0]);
        $this->assertArrayNotHasKey('introduction', $users[0]);
    }

    public function testRestFrontend()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/demo/users');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('table tbody tr')->count());
    }

    public function testContactForm()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/demo/contact');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->filter('form[ng-app="app"]')->count());
    }

    /**
     * @dataProvider provideContactSubmitData
     */
    public function testContactSend($submit, $statusCode, $errorFields)
    {
        $client = static::createClient();
        $submit['_token'] = $client->getContainer()->get('form.csrf_provider')->generateCsrfToken('contact');

        $client->request('POST', '/demo/contact.json', [
            'contact' => $submit
        ]);

        $this->assertEquals($statusCode, $client->getResponse()->getStatusCode());

        $errors = json_decode($client->getResponse()->getContent(), true);
        foreach ($errorFields as $field) {
            $this->assertArrayHasKey($field, $errors);
        }
    }

    /**
     * @return array
     */
    public function provideContactSubmitData()
    {
        return [
            [['email' => 'user@example.com', 'message' => 'foo'], 200, []],
            [['message' => 'foo'], 500, ['email']],
            [['email' => 'user@example.com'], 500, ['message']],
        ];
    }

    public function testSecureSection()
    {
        $client = static::createClient();

        // goes to the secure page
        $crawler = $client->request('GET', '/demo/secured/hello/World');

        // redirects to the login page
        $crawler = $client->followRedirect();

        // submits the login form
        $form = $crawler->selectButton('Login')->form(array('_username' => 'admin', '_password' => 'adminpass'));
        $client->submit($form);

        // redirect to the original page (but now authenticated)
        $crawler = $client->followRedirect();

        // check that the page is the right one
        $this->assertCount(1, $crawler->filter('h1.title:contains("Hello World!")'));

        // click on the secure link
        $link = $crawler->selectLink('Hello resource secured')->link();
        $crawler = $client->click($link);

        // check that the page is the right one
        $this->assertCount(1, $crawler->filter('h1.title:contains("secured for Admins only!")'));
    }
}
