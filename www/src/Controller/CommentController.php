<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use App\Entity\Comment;
use App\Service\AuthService;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;

// Для работы с HTTP кодом
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    const LENGTH_COMMENT_STRING = 512;
    #[Route('/comments/{articleId}', name: 'app_comments')]
    public function showComments($articleId, EntityManagerInterface $entityManager): Response
    {
        // Получение текущего пользователя из куки (для отображения)
        $authService = new AuthService($entityManager);
        $user = $authService->getCurrentUser();

        // Получение исходных данных коментария
        $articleRepository = $entityManager->getRepository(Article::class);
        $firstArticle = $articleRepository->findOneBy([], ['createdAt' => 'ASC']);

        // Получения всех комментариев к записи
        $comments = $entityManager->getRepository(Comment::class)->findBy(['article' => $articleId]);

        // Счётчик для комментариев
        $commentCount = count($comments);

        return $this->render('comment/comment.html.twig', [
            'comments' => $comments,
            'user' => $user,
            'first_article' => $firstArticle,
            'commentCount' => $commentCount,
        ]);
    }


    #[Route('/comment/add', name: 'comment_add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Получаю данные авторизации пользователя по токену
        $authService = new AuthService($entityManager);
        $user = $authService->getCurrentUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => "Пользователь не авторизован",
            ]);
        }

        $text = $request->get('text');

        if (empty($text) || mb_strlen($text) < 2)
        {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => "Не заполнено поле с текстом комментария",
            ]);
        }

        if (mb_strlen($text) > self::LENGTH_COMMENT_STRING)
        {
            $excessLength = mb_strlen($text) - self::LENGTH_COMMENT_STRING;
            $maxLenStr = self::LENGTH_COMMENT_STRING;

            return $this->json([
                'success' => false,
                'data' => [],
                'error' => "Поле с текстом комментария должно быть не более $maxLenStr символов. Сейчас лишних символов: $excessLength",
            ]);
        }

        // Получаю объект Article
        $articleId = $entityManager->getRepository(Article::class);
        $article = $articleId->findOneBy([], ['createdAt' => 'ASC']);
        //$articleId = $request->get('article_id');
        //$article = $entityManager->getRepository(Article::class)->find($articleId);

        if (!$article) {
            return $this->json([
                'success' => false,
                'data' => [],
                'error' => "Запись блога не найдена",
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