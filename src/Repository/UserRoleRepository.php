<?php

namespace App\Repository;

use App\Entity\UserRole;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserRole|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRole|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRole[]    findAll()
 * @method UserRole[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRoleRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserRole::class);
    }

    /**
     * Возвращает роль пользователя
     * 
     * @return UserRole|null
     */
    public function getRoleUser(): ?UserRole
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.code = :code')
            ->setParameter('code', UserRole::USER_ROLE_USER)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    /**
     * Возвращает роль администратора
     * 
     * @return UserRole|null
     */
    public function getRoleAdmin(): ?UserRole
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.code = :code')
            ->setParameter('code', UserRole::USER_ROLE_ADMIN)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
     /**
     * Возвращает роль для подключения через API
     * 
     * @return UserRole|null
     */
    public function getRoleAPI(): ?UserRole
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.code = :code')
            ->setParameter('code', UserRole::USER_ROLE_API)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
