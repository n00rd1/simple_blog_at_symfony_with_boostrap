<?php

namespace App\Repository;

use App\Entity\Article;
use App\Entity\ArticleLike;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ArticleLike>
 *
 * @method ArticleLike|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArticleLike|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArticleLike[]    findAll()
 * @method ArticleLike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArticleLike::class);
    }

    public function countByArticle(Article $article): int
    {
        return (int) $this->createQueryBuilder('al')
            ->select('COUNT(al.id)')
            ->andWhere('al.article = :article')
            ->setParameter('article', $article)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Article[] $articles
     *
     * @return array<int, int>
     */
    public function countByArticles(array $articles): array
    {
        if ($articles === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('al')
            ->select('IDENTITY(al.article) AS article_id, COUNT(al.id) AS like_count')
            ->andWhere('al.article IN (:articles)')
            ->groupBy('al.article')
            ->setParameter('articles', $articles)
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['article_id']] = (int) $row['like_count'];
        }

        return $counts;
    }

    /**
     * @param Article[] $articles
     *
     * @return list<int>
     */
    public function findArticleIdsLikedByUser(User $user, array $articles): array
    {
        if ($articles === []) {
            return [];
        }

        $rows = $this->createQueryBuilder('al')
            ->select('IDENTITY(al.article) AS article_id')
            ->andWhere('al.user = :user')
            ->andWhere('al.article IN (:articles)')
            ->setParameter('user', $user)
            ->setParameter('articles', $articles)
            ->getQuery()
            ->getArrayResult();

        return array_map(static fn (array $row): int => (int) $row['article_id'], $rows);
    }
}
