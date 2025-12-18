<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testRegisterSuccess(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test' . time() . '@example.com',
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

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('user', $data);
        $this->assertEquals($userData['name'], $data['user']['name']);
        $this->assertEquals($userData['email'], $data['user']['email']);
    }

    public function testRegisterValidationError(): void
    {
        $userData = [
            'name' => 'Test',
            'email' => 'invalid-email',
            'password' => '123',  // Too short
            'confirmPassword' => '456'  // Doesn't match
        ];

        $this->client->request(
            'POST',
            '/api/auth/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($userData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
    }

    public function testLoginSuccess(): void
    {
        // First, register a user
        $userData = [
            'name' => 'Login Test',
            'email' => 'login' . time() . '@example.com',
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

        // Then, login
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

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('token', $data);
        $this->assertArrayHasKey('user', $data);
    }

    public function testLoginInvalidCredentials(): void
    {
        $loginData = [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword'
        ];

        $this->client->request(
            'POST',
            '/api/auth/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($loginData)
        );

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
    }
}
