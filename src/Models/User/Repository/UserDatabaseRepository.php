<?php

namespace App\Models\User\Repository;

use App\Models\User\Contract\UserDatabaseRepositoryInterface;
use App\Models\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserDatabaseRepository extends ServiceEntityRepository implements UserDatabaseRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByEmail(string $email): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('lower(u.email) = :email')
            ->setParameter('email', mb_strtolower($email))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(User $user): void
    {
        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();
    }
}
