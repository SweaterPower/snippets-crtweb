<?php

namespace App\Repository;

use App\Entity\AuthTokens;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method AuthTokens|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuthTokens|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuthTokens[]    findAll()
 * @method AuthTokens[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthTokensRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, AuthTokens::class);
    }

    // /**
    //  * @return AuthTokens[] Returns an array of AuthTokens objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AuthTokens
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
