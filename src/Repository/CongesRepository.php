<?php

namespace App\Repository;

use App\Entity\Services;
use App\Entity\User;
use App\Entity\Conges;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Conges>
 */
class CongesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Conges::class);
    }

    public function findByDate(string $date): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where("DATE_FORMAT(c.startDate, '%Y-%m-%d') <= :date")
            ->andWhere("DATE_FORMAT(c.endDate, '%Y-%m-%d') >= :date")
            ->setParameter('date', $date)
            ->getQuery();
        return $qb->getResult();
    }

    public function findByDateService(string $date, Services $service): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where("DATE_FORMAT(c.startDate, '%Y-%m-%d') <= :date")
            ->andWhere("DATE_FORMAT(c.endDate, '%Y-%m-%d') >= :date")
            ->setParameter('date', $date)
            ->leftJoin("c.user", "u")
            ->andWhere("u.Service = :service")
            ->setParameter('service', $service)
            ->getQuery();
        return $qb->getResult();
    }

    public function findFutur(string $date): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where("DATE_FORMAT(c.startDate, '%Y-%m-%d') >= :date")
            ->setParameter('date', $date)
            ->orderBy('c.startDate', 'ASC')
            ->getQuery();
        return $qb->getResult();
    }

    public function findPasse(string $date): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where("DATE_FORMAT(c.endDate, '%Y-%m-%d') < :date")
            ->setParameter('date', $date)
            ->orderBy('c.endDate', 'DESC')
            ->getQuery();
        return $qb->getResult();
    }

    public function findByUserAndDate(int $userId, \DateTimeInterface $startDate, \DateTimeInterface $endDate): ?Conges
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->andWhere('c.startDate <= :endDate')
            ->andWhere('c.endDate >= :startDate')
            ->setParameter('user', $userId)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }


public function findByServiceId(int $serviceId): array
{
    return $this->createQueryBuilder('c')
        ->innerJoin('c.user', 'u')
        ->andWhere('u.Service = :serviceId') 
        ->setParameter('serviceId', $serviceId)
        ->getQuery()
        ->getResult();
}



    
    //    /**
    //     * @return Conges[] Returns an array of Conges objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Conges
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
