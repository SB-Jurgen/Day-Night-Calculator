<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DayNightControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('form');
    }

    public function testCalculateDayNightHours(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $token = $crawler->filter('input[name="time[_token]"]')->attr('value');
        $crawler = $client->request('POST', '/', [
            'time' => [
                'start_time' => '20:00',
                'end_time' => '02:00',
                '_token' => $token,
            ],
        ]);

        self::assertStringStartsWith('/success', $client->getResponse()->headers->get('Location'));
        $client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Success');
    }

    public function testSuccessWithoutData(): void
    {
        $client = static::createClient();
        $client->request('GET', '/success');
        $client->followRedirect();
        $this->assertStringContainsString('error=', $client->getHistory()->current()->getUri());
        self::assertSelectorExists('.error');
    }

    public function testCalculateWithInvalidData(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $token = $crawler->filter('input[name="time[_token]"]')->attr('value');
        $crawler = $client->request('POST', '/', [
            'time' => [
                'start_time' => '12:34',
                'end_time' => '02:00',
                '_token' => $token,
            ],
        ]);

        self::assertResponseIsUnprocessable();
        self::assertSelectorExists('.error');
    }
}
