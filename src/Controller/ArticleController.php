<?php

namespace App\Controller;

use App\Repository\ArticleRepository;
use Exception;
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
     * @throws Exception
     */
    public function addArticle(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $this->articleRepository->addArticle($data);

        return new JsonResponse(['message' => 'Article added successfully']);
    }

    /**
     * Get all active articles
     *
     * @Route("/active", methods={"GET"})
     */
    public function getActiveArticles(): JsonResponse
    {
        $articles = $this->articleRepository->findActiveArticles();

        return new JsonResponse($articles);
    }

    /**
     * Get filtered articles
     * Possible filters: date, active
     *
     * @Route("/filtered", methods={"GET"})
     * @throws Exception
     */
    public function getFilteredArticles(Request $request): JsonResponse
    {
        $filterDate = $request->query->get('date');
        $isActiveOnly = $request->query->get('active', '1');

        $articles = $this->articleRepository->findFilteredArticles($filterDate, $isActiveOnly);
        return new JsonResponse($articles);
    }

    /**
     * Get paginated articles
     * Possible params: page, per_page
     *
     * @Route("/paginated", methods={"GET"})
     */
    public function getPaginatedArticles(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $perPage = $request->query->getInt('per_page', 10);

        $result = $this->articleRepository->findPaginatedResults($page, $perPage);
        return new JsonResponse($result);
    }
}
