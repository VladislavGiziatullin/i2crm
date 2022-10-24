<?php

namespace App\Repository\Github;

use App\Entity\Github\UserRepo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserRepo>
 *
 * @method UserRepo|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRepo|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRepo[]    findAll()
 * @method UserRepo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserRepo::class);
    }

    public function save(UserRepo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param iterable|UserRepo[] $collection
     */
    public function saveBulk(iterable $collection): void
    {
        foreach ($collection as $item) {
            $this->save($item);
        }

        $this->getEntityManager()->flush();
    }

    public function remove(UserRepo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param iterable|ArrayCollection<UserRepo> $collection
     */
    public function removeBulk(iterable $collection)
    {
        foreach ($collection as $item) {
            $this->remove($item);
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @return iterable|ArrayCollection<UserRepo>
     */
    public function findByGithubUserAndNames(int $userId): iterable
    {
        $qb = $this->createQueryBuilder('gur');

        $query = $qb
            ->andWhere("gur.githubUserId = :userId")
            ->setParameter('userId', $userId)
            ->getQuery();

        return new ArrayCollection($query->getResult());
    }
}
