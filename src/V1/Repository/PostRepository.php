<?php

namespace App\V1\Repository;

use App\V1\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 *
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function findOneByApiId(int $apiId): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.api_id = :val')
            ->setParameter('val', $apiId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
