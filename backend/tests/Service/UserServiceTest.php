<?php

namespace App\Tests\Service;

use App\Service\UserService;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserServiceTest extends KernelTestCase
{
    private $userService;
    private $userRepository;
    private $passwordHasher;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->userService = $container->get(UserService::class);
        $this->userRepository = $container->get(UserRepository::class);
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
    }

    public function testCreateUserSuccess(): void
    {
        $email = 'service_test_' . time() . '@example.com';
        $userData = [
            'name' => 'Service Test User',
            'email' => $email,
            'password' => 'Password123!',
            'confirmPassword' => 'Password123!'
        ];

        $user = $this->userService->createUser($userData);

        $this->assertNotNull($user);
        $this->assertEquals($userData['name'], $user->getName());
        $this->assertEquals($userData['email'], $user->getEmail());
        $this->assertNotEquals($userData['password'], $user->getPassword()); // Should be hashed
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testCreateUserWithExistingEmail(): void
    {
        $email = 'duplicate_' . time() . '@example.com';
        $userData = [
            'name' => 'First User',
            'email' => $email,
            'password' => 'Password123!',
            'confirmPassword' => 'Password123!'
        ];

        // Create first user
        $this->userService->createUser($userData);

        // Try to create duplicate
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Email already exists');
        $this->userService->createUser($userData);
    }

    public function testUpdateUserSuccess(): void
    {
        // Create a user first
        $email = 'update_test_' . time() . '@example.com';
        $userData = [
            'name' => 'Original Name',
            'email' => $email,
            'password' => 'Password123!',
            'confirmPassword' => 'Password123!'
        ];

        $user = $this->userService->createUser($userData);
        $userId = $user->getId();

        // Update the user
        $updateData = [
            'name' => 'Updated Name',
            'email' => $email
        ];

        $updatedUser = $this->userService->updateUser($userId, $updateData);

        $this->assertEquals('Updated Name', $updatedUser->getName());
        $this->assertEquals($email, $updatedUser->getEmail());
    }

    public function testFindUserByEmail(): void
    {
        $email = 'find_test_' . time() . '@example.com';
        $userData = [
            'name' => 'Find Test',
            'email' => $email,
            'password' => 'Password123!',
            'confirmPassword' => 'Password123!'
        ];

        $createdUser = $this->userService->createUser($userData);

        $foundUser = $this->userRepository->findOneBy(['email' => $email]);

        $this->assertNotNull($foundUser);
        $this->assertEquals($createdUser->getId(), $foundUser->getId());
        $this->assertEquals($email, $foundUser->getEmail());
    }

    public function testPasswordIsHashed(): void
    {
        $email = 'hash_test_' . time() . '@example.com';
        $plainPassword = 'Password123!';
        $userData = [
            'name' => 'Hash Test',
            'email' => $email,
            'password' => $plainPassword,
            'confirmPassword' => $plainPassword
        ];

        $user = $this->userService->createUser($userData);

        // Password should be hashed
        $this->assertNotEquals($plainPassword, $user->getPassword());
        $this->assertStringStartsWith('$2y$', $user->getPassword()); // Bcrypt format
        
        // Verify password matches
        $this->assertTrue(
            $this->passwordHasher->isPasswordValid($user, $plainPassword)
        );
    }

    public function testUserHasDefaultRole(): void
    {
        $email = 'role_test_' . time() . '@example.com';
        $userData = [
            'name' => 'Role Test',
            'email' => $email,
            'password' => 'Password123!',
            'confirmPassword' => 'Password123!'
        ];

        $user = $this->userService->createUser($userData);

        $roles = $user->getRoles();
        $this->assertIsArray($roles);
        $this->assertContains('ROLE_USER', $roles);
    }
}
