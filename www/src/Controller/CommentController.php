<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentController extends AbstractController
{
    const LENGTH_COMMENT_STRING = 512;

    #[Route('/comments/{articleId}', name: 'app_comments')]
    public function showComments(
        $articleId,
        EntityManagerInterface $entityManager,
        TranslatorInterface $translator
    ): Response {
        $articleRepository = $entityManager->getRepository(Article::class);
        $firstArticle = $articleRepository->find($articleId);
        if (!$firstArticle) {
            throw $this->createNotFoundException($translator->trans('error.post_not_found'));
        }

        $comments = $entityManager->getRepository(Comment::class)->findBy(
            ['article' => $firstArticle],
            ['createdAt' => 'ASC']
        );

        return $this->render('comment/comment.html.twig', [
            'comments' => $comments,
            'user' => $this->getUser(),
            'first_article' => $firstArticle,
            'commentCount' => count($comments),
        ]);
    }

    #[Route('/comment/add', name: 'comment_add', methods: ['POST'])]
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

        if (!$this->isCsrfTokenValid('comment_add', (string) $request->request->get('_csrf_token'))) {
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
                'error' => $translator->trans('error.comment_text_required'),
            ]);
        }

        if (mb_strlen($text) > self::LENGTH_COMMENT_STRING) {
            $excessLength = mb_strlen($text) - self::LENGTH_COMMENT_STRING;

            return $this->json([
                'success' => false,
                'data' => [],
                'error' => $translator->trans('error.comment_text_too_long', [
                    '%max%' => self::LENGTH_COMMENT_STRING,
                    '%excess%' => $excessLength,
                ]),
            ]);
        }

        $article = $entityManager->getRepository(Article::class)->find($request->get('article_id'));
        if (!$article) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => $translator->trans('error.post_not_found'),
            ]);
        }

        $comment = new Comment();
        $comment->setAuthor($user);
        $comment->setArticle($article);
        $comment->setComment($text);
        $comment->setCreatedAt(new \DateTime());

        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->json(['comment_id' => $comment->getId()]);
    }
}
