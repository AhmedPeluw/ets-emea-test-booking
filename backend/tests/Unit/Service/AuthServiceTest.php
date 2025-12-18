<?php

namespace App\Tests\Unit\Service;

use App\Service\AuthService;
use App\Repository\UserRepository;
use App\Document\User;
use App\DTO\RegisterDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AuthServiceTest extends TestCase
{
    public function testRegisterCreatesUserSuccessfully(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $jwtManager = $this->createMock(JWTTokenManagerInterface::class);

        $userRepository->method('emailExists')->willReturn(false);
        $passwordHasher->method('hashPassword')->willReturn('hashed_password');
        $userRepository->expects($this->once())->method('save');

        $service = new AuthService($userRepository, $passwordHasher, $jwtManager);

        $dto = new RegisterDTO();
        $dto->name = 'Test User';
        $dto->email = 'test@example.com';
        $dto->password = 'password123';
        $dto->confirmPassword = 'password123';

        $user = $service->register($dto);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('test@example.com', $user->getEmail());
    }

    public function testRegisterThrowsExceptionWhenEmailExists(): void
    {
        $userRepository = $this->createMock(UserRepository::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $jwtManager = $this->createMock(JWTTokenManagerInterface::class);

        $userRepository->method('emailExists')->willReturn(true);

        $service = new AuthService($userRepository, $passwordHasher, $jwtManager);

        $dto = new RegisterDTO();
        $dto->email = 'existing@example.com';

        $this->expectException(\Symfony\Component\HttpKernel\Exception\BadRequestHttpException::class);
        $service->register($dto);
    }
}
