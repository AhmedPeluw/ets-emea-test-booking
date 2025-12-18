<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class BookingControllerTest extends WebTestCase
{
    private $client;
    private $token;
    private $userId;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->token = $this->createUserAndGetToken();
    }

    private function createUserAndGetToken(): string
    {
        $userData = [
            'name' => 'Booking Test User',
            'email' => 'booking' . time() . '@example.com',
            'password' => 'Password123!',
            'confirmPassword' => 'Password123!'
        ];

        $this->client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $loginData = [
            'email' => $userData['email'],
            'password' => $userData['password']
        ];

        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($loginData)
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->userId = $data['user']['id'];
        return $data['token'];
    }

    private function getAvailableSessionId(): ?string
    {
        $this->client->request('GET', '/api/sessions?itemsPerPage=1');
        $data = json_decode($this->client->getResponse()->getContent(), true);

        if (!empty($data['data']['items'])) {
            return $data['data']['items'][0]['id'];
        }

        return null;
    }

    public function testCreateBookingSuccess(): void
    {
        $sessionId = $this->getAvailableSessionId();

        if (!$sessionId) {
            $this->markTestSkipped('No sessions available to book');
        }

        $bookingData = ['sessionId' => $sessionId];

        $this->client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
            ],
            json_encode($bookingData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('confirmed', $data['data']['status']);
        $this->assertEquals($sessionId, $data['data']['sessionId']);
    }

    public function testCreateBookingUnauthorized(): void
    {
        $sessionId = $this->getAvailableSessionId();

        if (!$sessionId) {
            $this->markTestSkipped('No sessions available to book');
        }

        $bookingData = ['sessionId' => $sessionId];

        $this->client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($bookingData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testCreateBookingMissingSessionId(): void
    {
        $this->client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
            ],
            json_encode([])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
    }

    public function testCreateBookingInvalidSessionId(): void
    {
        $fakeSessionId = '000000000000000000000000';
        $bookingData = ['sessionId' => $fakeSessionId];

        $this->client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
            ],
            json_encode($bookingData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
    }

    public function testCreateDuplicateBooking(): void
    {
        $sessionId = $this->getAvailableSessionId();

        if (!$sessionId) {
            $this->markTestSkipped('No sessions available to book');
        }

        $bookingData = ['sessionId' => $sessionId];

        // Create first booking
        $this->client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
            ],
            json_encode($bookingData)
        );

        // Try to create duplicate booking
        $this->client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
            ],
            json_encode($bookingData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CONFLICT, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
    }

    public function testGetUserBookings(): void
    {
        $this->client->request(
            'GET',
            '/api/bookings',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('items', $data['data']);
        $this->assertArrayHasKey('total', $data['data']);
        $this->assertIsArray($data['data']['items']);
    }

    public function testGetUserBookingsWithPagination(): void
    {
        $this->client->request(
            'GET',
            '/api/bookings?page=1&itemsPerPage=5',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertLessThanOrEqual(5, count($data['data']['items']));
    }

    public function testGetUserBookingsUnauthorized(): void
    {
        $this->client->request('GET', '/api/bookings');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testCancelBookingSuccess(): void
    {
        // First create a booking
        $sessionId = $this->getAvailableSessionId();

        if (!$sessionId) {
            $this->markTestSkipped('No sessions available to book');
        }

        $bookingData = ['sessionId' => $sessionId];

        $this->client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
            ],
            json_encode($bookingData)
        );

        $createData = json_decode($this->client->getResponse()->getContent(), true);
        $bookingId = $createData['data']['id'];

        // Now cancel it
        $this->client->request(
            'DELETE',
            "/api/bookings/{$bookingId}",
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
    }

    public function testCancelNonExistentBooking(): void
    {
        $fakeBookingId = '000000000000000000000000';

        $this->client->request(
            'DELETE',
            "/api/bookings/{$fakeBookingId}",
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
            ]
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
    }

    public function testCancelBookingUnauthorized(): void
    {
        $fakeBookingId = '000000000000000000000000';

        $this->client->request('DELETE', "/api/bookings/{$fakeBookingId}");

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testBookingDataStructure(): void
    {
        // Create a booking first
        $sessionId = $this->getAvailableSessionId();

        if (!$sessionId) {
            $this->markTestSkipped('No sessions available to book');
        }

        $this->client->request(
            'POST',
            '/api/bookings',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
            ],
            json_encode(['sessionId' => $sessionId])
        );

        // Get bookings
        $this->client->request(
            'GET',
            '/api/bookings',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $this->token]
        );

        $data = json_decode($this->client->getResponse()->getContent(), true);

        if (!empty($data['data']['items'])) {
            $booking = $data['data']['items'][0];

            // Verify all required fields
            $requiredFields = [
                'id', 'userId', 'sessionId', 'status', 
                'createdAt', 'updatedAt', 'session'
            ];

            foreach ($requiredFields as $field) {
                $this->assertArrayHasKey($field, $booking, "Booking should have field: {$field}");
            }

            // Verify session is populated
            $this->assertIsArray($booking['session']);
            $this->assertArrayHasKey('language', $booking['session']);
            $this->assertArrayHasKey('level', $booking['session']);

            // Verify status is valid
            $this->assertContains($booking['status'], ['confirmed', 'cancelled']);
        }
    }
}
