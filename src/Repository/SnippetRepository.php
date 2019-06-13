<?php

namespace App\Repository;

use App\Entity\Snippet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\ORM\QueryBuilder;

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
     * Возвращает конструктор запроса, который находит все сниппеты
     * 
     * @return QueryBuilder
     */
    public function getFindAllQueryBuilder()
    {
        return $this->createQueryBuilder('s');
    }

    /**
     * Возвращает конструктор запроса, который находит все сниппеты для указанного владельца
     * 
     * @return QueryBuilder
     */
    public function getFindByOwnerQueryBuilder($value)
    {
        return $this->createQueryBuilder('s')
                ->andWhere('s.owner = :val')
                ->setParameter('val', $value);
    }

    /**
     * Возвращает конструктор запроса, который находит все публичные сниппеты
     * 
     * @return QueryBuilder
     */
    public function getFindPublicOnlyQueryBuilder()
    {
        return $this->createQueryBuilder('s')
                ->andWhere('s.isPrivate = 0');
    }

    /**
     * Возвращает конструктор запроса, который находит все публичные сниппеты вместе со сниппетами указанного пользователя
     * 
     * @return QueryBuilder
     */
    public function getFindPublicAndOwnQueryBuilder($value)
    {
        return $this->createQueryBuilder('s')
                ->orWhere('s.owner = :val')
                ->orWhere('s.isPrivate = 0')
                ->setParameter('val', $value);
    }

    /**
     * Возвращает все сниппеты для указанного владельца
     * 
     * @param int $value
     * @return Snippet[]
     */
    public function findByOwner($value)
    {
        return $this->getFindByOwnerQueryBuilder($value)
                ->getQuery()
                ->getResult()
        ;
    }

    /**
     * Возвращает только публичные сниппеты
     * 
     * @return Snippet[]
     */
    public function findPublicOnly()
    {
        return $this->getFindPublicOnlyQueryBuilder()
                ->getQuery()
                ->getResult()
        ;
    }

    /**
     * Возвращает все публичные сниппеты вместе со сниппетами указанного пользователя
     * 
     * @return Snippet[]
     */
    public function findPublicAndOwn($value)
    {
        return $this->getFindPublicAndOwnQueryBuilder($value)
                ->getQuery()
                ->getResult();
    }

}
