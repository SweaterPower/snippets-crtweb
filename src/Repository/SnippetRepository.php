<?php

namespace App\Repository;

use App\Entity\Snippet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Snippet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Snippet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Snippet[]    findAll()
 * @method Snippet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SnippetRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Snippet::class);
    }

    /**
     * Возвращает все сниппеты для указанного владельца
     * 
     * @param int $value
     * @return Snippet[]
     */
    public function findByOwner($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.owner = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
        ;
    }
    
    /**
     * Возвращает только публичные сниппеты
     * 
     * @return Snippet[]
     */
    public function getPublicOnly()
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.isPrivate = 0')
            ->getQuery()
            ->getResult()
        ;
    }
}
