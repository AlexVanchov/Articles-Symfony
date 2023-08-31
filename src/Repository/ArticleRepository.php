<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @extends ServiceEntityRepository<Article>
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
     * Add an article to the database by creating new object from the request
     *
     * @param array $data
     * @throws Exception
     */
    public function addArticle(array $data): void
    {
        $article = new Article();
        $article->setTitle($data['title']);
        $article->setContent($data['content']);
        $article->setPublishAt(new \DateTime($data['publish_at']));
        $article->setStatus($data['status']);
        $article->setCreatedAt(new \DateTime());

        $this->add($article, true);
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
            $filterStartDateTime = clone $filterDateTime;
            $filterEndDateTime = clone $filterDateTime;
            $filterStartDateTime->setTime(0, 0, 0);
            $filterEndDateTime->setTime(23, 59, 59);

            $qb->andWhere($qb->expr()->between('a.publish_at', ':start', ':end'))
                ->setParameter('start', $filterStartDateTime)
                ->setParameter('end', $filterEndDateTime);
        }

        $search_status = $isActiveOnly === '1' ? 'active' : "inactive";
        $qb->andWhere('a.status =:statusActive')
            ->setParameter('statusActive', $search_status);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Gets all active articles
     *
     * @return array
     */
    public function findActiveArticles(): array
    {
        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.status = :statusActive')
            ->setParameter('statusActive', 'active');

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Gets articles by pages
     *
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function findPaginatedResults(int $page, int $perPage): array
    {
        $query = $this->createQueryBuilder('a')
            ->getQuery();

        $paginator = new Paginator($query);
        return $paginator
            ->getQuery()
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage)
            ->getArrayResult();
    }
}
