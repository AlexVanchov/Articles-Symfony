<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @extends ServiceEntityRepository<Article>
 *
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    /**
     * Add an article to the database
     *
     * @param Article $entity
     * @param bool $flush
     */
    public function add(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Remove an article from the database
     *
     * @param Article $entity
     * @param bool $flush
     */
    public function remove(Article $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Format article data for JSON response
     *
     * @param $filterDate
     * @param $isActiveOnly
     * @return array
     * @throws Exception
     */
    public function findFilteredArticles($filterDate, $isActiveOnly): array
    {
        $qb = $this->createQueryBuilder('a');

        if ($filterDate) {
            $filterDateTime = new \DateTime($filterDate);
            $qb->andWhere('a.publish_at >= :filterDate')
                ->setParameter('filterDate', $filterDateTime->format('Y-m-d 00:00:00'));
        }

        if ($isActiveOnly) {
            $qb->andWhere('a.status = :statusActive')
                ->setParameter('statusActive', 'active');
        }

        return $qb->getQuery()->getResult();
    }
}
