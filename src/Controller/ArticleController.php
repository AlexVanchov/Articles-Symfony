<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/articles", name="api_articles")
 */

class ArticleController extends AbstractController
{
    private ArticleRepository $articleRepository;

    public function __construct(ArticleRepository $articleRepository)
    {
        $this->articleRepository = $articleRepository;
    }

    /**
     * Add an article
     *
     * @Route("/add", methods={"POST"})
     */
    public function addArticle(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $article = new Article();
        $article->setTitle($data['title']);
        $article->setContent($data['content']);
        $article->setPublishAt(new \DateTime($data['publish_at'])); // Set the publish_at value
        $article->setStatus($data['status']); // Set the status value
        $article->setCreatedAt(new \DateTime()); // Automatically set the created_at value to the current timestamp

        $this->articleRepository->add($article, true);

        return new JsonResponse(['message' => 'Article added successfully']);
    }

    /**
     * Get all active articles
     *
     * @Route("/active", methods={"GET"})
     */
    public function getActiveArticles(): JsonResponse
    {
        $articles = $this->articleRepository->findBy(['status' => 'active']);

        $data = [];
        foreach ($articles as $article) {
            $data[] = $this->formatArticleData($article);
        }

        return new JsonResponse($data);
    }

    /**
     * Get filtered articles
     * Possible filters: date, active
     *
     * @Route("/filtered", methods={"GET"})
     */
    public function getFilteredArticles(Request $request): JsonResponse
    {
        $filterDate = $request->query->get('date');
        $isActiveOnly = $request->query->get('active', false);

        $articles = $this->articleRepository->findFilteredArticles($filterDate, $isActiveOnly);

        $data = [];
        foreach ($articles as $article) {
            $data[] = $this->formatArticleData($article);
        }

        return new JsonResponse($data);
    }

    /**
     * TODO - Add pagination in sql query not get-all and paginate in php
     * Get paginated articles
     * Possible params: page, per_page
     *
     * @Route("/paginated", methods={"GET"})
     */
    public function getPaginatedArticles(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('per_page', 10);

        $articles = $this->articleRepository->findAll();

        $paginatedArticles = $this->paginate($articles, $page, $perPage);

        $data = [];
        foreach ($paginatedArticles as $article) {
            $data[] = $this->formatArticleData($article);
        }

        return new JsonResponse($data);
    }

    /**
     * Format article data for response
     *
     * @param Article $article
     * @return array
     */
    private function formatArticleData(Article $article): array
    {
        $published_on = $article->getPublishAt();
        return [
            'title' => $article->getTitle(),
            'content' => $article->getContent(),
            'created_at' => $article->getCreatedAt()->format('Y-m-d H:i:s'),
            'publish_at' => $published_on !== null ? $published_on->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * Paginate an array of items
     *
     * @param array $items
     * @param int $page
     * @param int $perPage
     * @return array
     */
    private function paginate(array $items, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        return array_slice($items, $offset, $perPage);
    }
}
