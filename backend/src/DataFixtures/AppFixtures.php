<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Document\User;
use App\Document\Session;
use Doctrine\Bundle\MongoDBBundle\Fixture\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setName('John Doe');
        $user->setEmail('user@test.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $manager->persist($user);

        $admin = new User();
        $admin->setName('Admin User');
        $admin->setEmail('admin@test.com');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->addRole('ROLE_ADMIN');
        $manager->persist($admin);

        $languages = ['Anglais', 'Français', 'Espagnol', 'Allemand', 'Italien'];
        $levels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
        $locations = ['Paris - Centre ETS', 'Lyon - Centre ETS', 'Marseille - Centre ETS'];

        for ($i = 0; $i < 20; $i++) {
            $session = new Session();
            $session->setLanguage($languages[array_rand($languages)]);
            $date = new \DateTime();
            $date->modify('+' . rand(1, 60) . ' days');
            $session->setDate($date);
            $hour = str_pad((string)rand(8, 18), 2, '0', STR_PAD_LEFT);
            $session->setTime($hour . ':30');
            $session->setLocation($locations[array_rand($locations)]);
            $session->setTotalSeats(rand(10, 30));
            $session->setLevel($levels[array_rand($levels)]);
            $session->setDurationMinutes(120);
            $session->setPrice((float)rand(80, 200));
            $session->setDescription('Session ' . $session->getLanguage());
            $manager->persist($session);
        }

        $manager->flush();
    }
}
