<?php

declare(strict_types=1);

namespace App\Repository;

use App\Document\Session;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

class SessionRepository extends DocumentRepository
{
    public function __construct(DocumentManager $dm)
    {
        $uow = $dm->getUnitOfWork();
        $classMetadata = $dm->getClassMetadata(Session::class);
        parent::__construct($dm, $uow, $classMetadata);
    }

    public function findAvailableWithPagination(
        int $page = 1,
        int $itemsPerPage = 10,
        ?string $language = null,
        ?string $level = null
    ): array {
        // Construire la requête de base
        $criteria = [
            'isActive' => true,
            'date' => ['$gte' => new \DateTime('today')],
            'availableSeats' => ['$gt' => 0]
        ];

        if ($language) {
            $criteria['language'] = $language;
        }

        if ($level) {
            $criteria['level'] = $level;
        }

        // Compter le total
        $totalCount = $this->createQueryBuilder()
            ->setQueryArray($criteria)
            ->count()
            ->getQuery()
            ->execute();

        // Récupérer les items avec pagination
        $offset = ($page - 1) * $itemsPerPage;
        $items = $this->createQueryBuilder()
            ->setQueryArray($criteria)
            ->sort('date', 'asc')
            ->sort('time', 'asc')
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
            ->sort('date', 'desc')
            ->sort('time', 'desc')
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

    public function findByLanguage(string $language): array
    {
        return $this->createQueryBuilder()
            ->field('language')->equals($language)
            ->field('isActive')->equals(true)
            ->field('date')->gte(new \DateTime('today'))
            ->sort('date', 'asc')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function findUpcoming(int $limit = 10): array
    {
        return $this->createQueryBuilder()
            ->field('isActive')->equals(true)
            ->field('date')->gte(new \DateTime('today'))
            ->field('availableSeats')->gt(0)
            ->sort('date', 'asc')
            ->sort('time', 'asc')
            ->limit($limit)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function countAvailable(): int
    {
        return $this->createQueryBuilder()
            ->field('isActive')->equals(true)
            ->field('date')->gte(new \DateTime('today'))
            ->field('availableSeats')->gt(0)
            ->count()
            ->getQuery()
            ->execute();
    }

    public function findByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder()
            ->field('date')->gte($startDate)
            ->field('date')->lte($endDate)
            ->field('isActive')->equals(true)
            ->sort('date', 'asc')
            ->sort('time', 'asc')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function save(Session $session): void
    {
        $this->getDocumentManager()->persist($session);
        $this->getDocumentManager()->flush();
    }

    public function remove(Session $session): void
    {
        $this->getDocumentManager()->remove($session);
        $this->getDocumentManager()->flush();
    }

    public function findById(string $id): ?Session
    {
        return $this->find($id);
    }
}
