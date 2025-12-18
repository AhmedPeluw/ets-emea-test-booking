<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

/**
 * UserRepository - Design Pattern: Repository
 * 
 * Abstraction de la couche de persistance pour les utilisateurs
 */
class UserRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        $classMetadata = $dm->getClassMetadata(User::class);
        parent::__construct($dm, $uow, $classMetadata);
    }

    /**
     * Trouve un utilisateur par son email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => strtolower($email)]);
    }

    /**
     * Vérifie si un email existe déjà
     */
    public function emailExists(string $email, ?string $excludeUserId = null): bool
    {
        $qb = $this->createQueryBuilder()
            ->field('email')->equals(strtolower($email));

        if ($excludeUserId) {
            $qb->field('id')->notEqual($excludeUserId);
        }

        // Correction: utiliser count() sur le QueryBuilder, pas sur la Query
        return $qb->count()->getQuery()->execute() > 0;
    }

    /**
     * Trouve les utilisateurs créés après une date donnée
     */
    public function findRecentUsers(\DateTime $since): array
    {
        return $this->createQueryBuilder()
            ->field('createdAt')->gte($since)
            ->sort('createdAt', 'desc')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    /**
     * Compte le nombre total d'utilisateurs
     */
    public function countAll(): int
    {
        return $this->createQueryBuilder()
            ->count()
            ->getQuery()
            ->execute();
    }

    /**
     * Sauvegarde un utilisateur
     */
    public function save(User $user): void
    {
        $this->getDocumentManager()->persist($user);
        $this->getDocumentManager()->flush();
    }

    /**
     * Supprime un utilisateur
     */
    public function remove(User $user): void
    {
        $this->getDocumentManager()->remove($user);
        $this->getDocumentManager()->flush();
    }
}
