<?php

namespace App\Repository;

use App\Entity\UserStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserStatus[]    findAll()
 * @method UserStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserStatusRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserStatus::class);
    }
    
    /**
     * Возвращает активный статус
     * 
     * @return UserStatus|null
     */
    public function getActiveStatus(): ?UserStatus
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.code = :code')
            ->setParameter('code', UserStatus::ACTIVE_STATUS_CODE)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    /**
     * Возвращает не подтвержденный статус
     * 
     * @return UserStatus|null
     */
    public function getNotConfirmedStatus(): ?UserStatus
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.code = :code')
            ->setParameter('code', UserStatus::NOT_CONFIRMED_STATUS_CODE)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    /**
     * Возвращает не активный статус
     * 
     * @return UserStatus|null
     */
    public function getNotActiveStatus(): ?UserStatus
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.code = :code')
            ->setParameter('code', UserStatus::NOT_ACTIVE_STATUS_CODE)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
