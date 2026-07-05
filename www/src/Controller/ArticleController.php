<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ArticleLike;
use App\Entity\User;
use App\Repository\ArticleLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArticleController extends AbstractController
{
    const LENGTH_TEXT_STRING = 512;

    #[Route('/', name: 'app_article')]
    public function index(EntityManagerInterface $entityManager, ArticleLikeRepository $likeRepository): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findBy([], ['createdAt' => 'DESC']);
        $user = $this->getUser();

        return $this->render('article/article_list.html.twig', [
            'controller_name' => 'ArticleController',
            'articles' => $articles,
            'user' => $user,
            'likeCounts' => $likeRepository->countByArticles($articles),
            'likedArticleIds' => $user instanceof User ? $likeRepository->findArticleIdsLikedByUser($user, $articles) : [],
        ]);
    }

    #[Route('/article/add', name: 'article_add', methods: ['POST'])]
    public function add(
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => $translator->trans('error.user_not_authorized'),
            ]);
        }

        if (!$this->isCsrfTokenValid('article_add', (string) $request->request->get('_csrf_token'))) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => $translator->trans('error.invalid_csrf'),
            ]);
        }

        $text = $request->get('text');

        if (empty($text) || mb_strlen($text) < 2) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => $translator->trans('error.post_text_required'),
            ]);
        }

        if (mb_strlen($text) > self::LENGTH_TEXT_STRING) {
            $excessLength = mb_strlen($text) - self::LENGTH_TEXT_STRING;
            $maxLenStr = self::LENGTH_TEXT_STRING;

            return $this->json([
                'success' => false,
                'data' => [],
                'error' => $translator->trans('error.post_text_too_long', [
                    '%max%' => $maxLenStr,
                    '%excess%' => $excessLength,
                ]),
            ]);
        }

        $article = new Article();
        $article->setAuthor($user);
        $article->setText($request->get('text'));
        $article->setCreatedAt(new \DateTime());

        $entityManager->persist($article);
        $entityManager->flush();

        return $this->json(['article_id' => $article->getId()]);
    }

    #[Route('/article/{id}/like', name: 'article_like', methods: ['POST'])]
    public function toggleLike(
        Article $article,
        Request $request,
        EntityManagerInterface $entityManager,
        ArticleLikeRepository $likeRepository,
        TranslatorInterface $translator
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => $translator->trans('error.user_not_authorized'),
            ]);
        }

        if (!$this->isCsrfTokenValid('article_like', (string) $request->request->get('_csrf_token'))) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => $translator->trans('error.invalid_csrf'),
            ]);
        }

        $existingLike = $likeRepository->findOneBy(['article' => $article, 'user' => $user]);
        $liked = false;

        if ($existingLike instanceof ArticleLike) {
            $entityManager->remove($existingLike);
        } else {
            $like = new ArticleLike();
            $like->setArticle($article);
            $like->setUser($user);
            $like->setCreatedAt(new \DateTime());
            $entityManager->persist($like);
            $liked = true;
        }

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'data' => [
                'liked' => $liked,
                'like_count' => $likeRepository->countByArticle($article),
            ],
        ]);
    }

    #[Route('/article/{id}/delete', name: 'article_delete', methods: ['POST'])]
    public function remove(
        Article $article,
        Request $request,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        $user = $this->getUser();
        if (!$user instanceof User || ($article->getAuthor()?->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN'))) {
            throw $this->createAccessDeniedException($translator->trans('error.user_not_authorized'));
        }

        if (!$this->isCsrfTokenValid('article_delete_'.$article->getId(), (string) $request->request->get('_csrf_token'))) {
            throw $this->createAccessDeniedException($translator->trans('error.invalid_csrf'));
        }

        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('app_article');
    }
}
