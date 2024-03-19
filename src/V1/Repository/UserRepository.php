<?php

namespace App\V1\Repository;

use App\V1\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByApiId(int $apiId): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.api_id = :val')
            ->setParameter('val', $apiId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByFields(array $fields): array
    {
        $queryBuilder = $this->createQueryBuilder('u');

        foreach ($fields as $field => $value) {
            if ($value) {
                $queryBuilder->andWhere("u.$field = :$field")
                ->setParameter($field, $value);
            }
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
