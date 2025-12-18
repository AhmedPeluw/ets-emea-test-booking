<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\Booking;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class BookingRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        $classMetadata = $dm->getClassMetadata(Booking::class);
        parent::__construct($dm, $uow, $classMetadata);
    }

    public function findByUser(string $userId): array
    {
        return $this->createQueryBuilder()
            ->field('userId')->equals($userId)
            ->sort('createdAt', 'desc')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function findActiveByUser(string $userId): array
    {
        return $this->createQueryBuilder()
            ->field('userId')->equals($userId)
            ->field('status')->in(['confirmed', 'pending'])
            ->sort('createdAt', 'desc')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function findBySession(string $sessionId): array
    {
        return $this->createQueryBuilder()
            ->field('sessionId')->equals($sessionId)
            ->sort('createdAt', 'desc')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function findConfirmedBySession(string $sessionId): array
    {
        return $this->createQueryBuilder()
            ->field('sessionId')->equals($sessionId)
            ->field('status')->equals('confirmed')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function userHasBookedSession(string $userId, string $sessionId): bool
    {
        $booking = $this->findOneBy([
            'userId' => $userId,
            'sessionId' => $sessionId,
            'status' => ['$in' => ['confirmed', 'pending']]
        ]);

        return $booking !== null;
    }

    public function countBySession(string $sessionId): int
    {
        return $this->createQueryBuilder()
            ->field('sessionId')->equals($sessionId)
            ->field('status')->in(['confirmed', 'pending'])
            ->count()
            ->getQuery()
            ->execute();
    }

    public function findByIdAndUser(string $bookingId, string $userId): ?Booking
    {
        return $this->findOneBy([
            'id' => $bookingId,
            'userId' => $userId
        ]);
    }

    public function findByUserWithPagination(
        string $userId,
        int $page = 1,
        int $itemsPerPage = 10
    ): array {
        $criteria = ['userId' => $userId];

        // Compter le total
        $totalCount = $this->createQueryBuilder()
            ->setQueryArray($criteria)
            ->count()
            ->getQuery()
            ->execute();

        // Récupérer les items
        $offset = ($page - 1) * $itemsPerPage;
        $items = $this->createQueryBuilder()
            ->setQueryArray($criteria)
            ->sort('createdAt', 'desc')
            ->skip($offset)
            ->limit($itemsPerPage)
            ->getQuery()
            ->execute()
            ->toArray();

        return [
            'items' => array_values($items),
            'total' => $totalCount,
            'pages' => (int) ceil($totalCount / $itemsPerPage),
            'currentPage' => $page,
            'itemsPerPage' => $itemsPerPage
        ];
    }

    public function findAllWithPagination(int $page = 1, int $itemsPerPage = 10): array
    {
        // Compter le total
        $totalCount = $this->createQueryBuilder()
            ->count()
            ->getQuery()
            ->execute();

        // Récupérer les items
        $offset = ($page - 1) * $itemsPerPage;
        $items = $this->createQueryBuilder()
            ->sort('createdAt', 'desc')
            ->skip($offset)
            ->limit($itemsPerPage)
            ->getQuery()
            ->execute()
            ->toArray();

        return [
            'items' => array_values($items),
            'total' => $totalCount,
            'pages' => (int) ceil($totalCount / $itemsPerPage),
            'currentPage' => $page,
            'itemsPerPage' => $itemsPerPage
        ];
    }

    public function save(Booking $booking): void
    {
        $this->getDocumentManager()->persist($booking);
        $this->getDocumentManager()->flush();
    }

    public function remove(Booking $booking): void
    {
        $this->getDocumentManager()->remove($booking);
        $this->getDocumentManager()->flush();
    }
}
