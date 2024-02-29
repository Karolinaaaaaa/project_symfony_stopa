<?php

namespace App\Tests\ICalendar\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class IcalendarControllerTest extends WebTestCase
{
    public function testFetchIcalendar()
    {
        $client = static::createClient();

        $url = 'https://slowhop.com/icalendar-export/api-v1/21c0ed902d012461d28605cdb2a8b7a2.ics';

        $client->request('GET', '/api/icalendar', ['url' => $url]);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->assertJson($client->getResponse()->getContent());

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('s3Url', $responseData);
        $this->assertArrayHasKey('data', $responseData);

        $this->assertTrue($responseData['success']);
        $this->assertNotEmpty($responseData['s3Url']);
        $this->assertIsArray($responseData['data']);

        foreach ($responseData['data'] as $event) {
            $this->assertArrayHasKey('id', $event);
            $this->assertArrayHasKey('start', $event);
            $this->assertArrayHasKey('end', $event);
            $this->assertArrayHasKey('summary', $event);
        }

    }
}
