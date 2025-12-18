<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    private $client;
    private $token;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->token = $this->createUserAndGetToken();
    }

    private function createUserAndGetToken(): string
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'user' . time() . '@example.com',
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
        return $data['token'];
    }

    public function testGetProfileSuccess(): void
    {
        $this->client->request(
            'GET',
            '/api/users/me',
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
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('email', $data['user']);
    }

    public function testGetProfileUnauthorized(): void
    {
        $this->client->request('GET', '/api/users/me');

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }

    public function testUpdateProfileSuccess(): void
    {
        $updateData = [
            'name' => 'Updated Name'
        ];

        $this->client->request(
            'PUT',
            '/api/users/me',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token
            ],
            json_encode($updateData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Updated Name', $data['user']['name']);
    }
}
