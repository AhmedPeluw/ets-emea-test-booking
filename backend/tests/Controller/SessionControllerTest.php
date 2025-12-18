<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class SessionControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testGetAllSessions(): void
    {
        $this->client->request('GET', '/api/sessions');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('items', $data['data']);
        $this->assertArrayHasKey('total', $data['data']);
        $this->assertArrayHasKey('pages', $data['data']);
    }

    public function testGetSessionsWithPagination(): void
    {
        $this->client->request('GET', '/api/sessions?page=1&itemsPerPage=5');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertLessThanOrEqual(5, count($data['data']['items']));
        $this->assertEquals(1, $data['data']['currentPage']);
        $this->assertEquals(5, $data['data']['itemsPerPage']);
    }

    public function testGetSessionsWithLanguageFilter(): void
    {
        $this->client->request('GET', '/api/sessions?language=English');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);

        // Verify all returned sessions are English
        foreach ($data['data']['items'] as $session) {
            $this->assertEquals('English', $session['language']);
        }
    }

    public function testGetSessionsWithLevelFilter(): void
    {
        $this->client->request('GET', '/api/sessions?level=B2');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);

        // Verify all returned sessions are B2 level
        foreach ($data['data']['items'] as $session) {
            $this->assertEquals('B2', $session['level']);
        }
    }

    public function testGetSessionsWithMultipleFilters(): void
    {
        $this->client->request('GET', '/api/sessions?language=English&level=B2&page=1&itemsPerPage=10');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);

        foreach ($data['data']['items'] as $session) {
            $this->assertEquals('English', $session['language']);
            $this->assertEquals('B2', $session['level']);
        }
    }

    public function testGetSessionById(): void
    {
        // First, get a list of sessions to get a valid ID
        $this->client->request('GET', '/api/sessions?itemsPerPage=1');
        $listData = json_decode($this->client->getResponse()->getContent(), true);

        if (!empty($listData['data']['items'])) {
            $sessionId = $listData['data']['items'][0]['id'];

            // Now get the specific session
            $this->client->request('GET', "/api/sessions/{$sessionId}");

            $response = $this->client->getResponse();
            $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

            $data = json_decode($response->getContent(), true);
            $this->assertTrue($data['success']);
            $this->assertArrayHasKey('data', $data);
            $this->assertEquals($sessionId, $data['data']['id']);
            $this->assertArrayHasKey('language', $data['data']);
            $this->assertArrayHasKey('level', $data['data']);
            $this->assertArrayHasKey('date', $data['data']);
            $this->assertArrayHasKey('time', $data['data']);
            $this->assertArrayHasKey('location', $data['data']);
            $this->assertArrayHasKey('availableSeats', $data['data']);
        } else {
            $this->markTestSkipped('No sessions available to test with');
        }
    }

    public function testGetNonExistentSession(): void
    {
        $fakeId = '000000000000000000000000'; // MongoDB ObjectId format

        $this->client->request('GET', "/api/sessions/{$fakeId}");

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
    }

    public function testSessionDataStructure(): void
    {
        $this->client->request('GET', '/api/sessions?itemsPerPage=1');

        $data = json_decode($this->client->getResponse()->getContent(), true);

        if (!empty($data['data']['items'])) {
            $session = $data['data']['items'][0];

            // Verify all required fields are present
            $requiredFields = [
                'id', 'language', 'level', 'date', 'time', 
                'duration', 'location', 'maxSeats', 'availableSeats', 
                'price', 'isActive'
            ];

            foreach ($requiredFields as $field) {
                $this->assertArrayHasKey($field, $session, "Session should have field: {$field}");
            }

            // Verify field types
            $this->assertIsString($session['id']);
            $this->assertIsString($session['language']);
            $this->assertIsString($session['level']);
            $this->assertIsString($session['date']);
            $this->assertIsString($session['time']);
            $this->assertIsInt($session['duration']);
            $this->assertIsString($session['location']);
            $this->assertIsInt($session['maxSeats']);
            $this->assertIsInt($session['availableSeats']);
            $this->assertIsNumeric($session['price']);
            $this->assertIsBool($session['isActive']);
        }
    }

    public function testInvalidPaginationParameters(): void
    {
        $this->client->request('GET', '/api/sessions?page=-1&itemsPerPage=0');

        $response = $this->client->getResponse();
        // Should still return 200 but with default pagination
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
    }
}
