<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Service\AuthService;
use Doctrine\ORM\EntityManagerInterface;

// Для работы с HTTP кодом
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Для хеширования пароля
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $authService = new AuthService($entityManager);
        $user = $authService->getCurrentUser();
        $articles = $entityManager->getRepository(Article::class)->findAll();

        return $this->render('article/article_list.html.twig', [
            'controller_name' => 'ArticleController',
            'articles' => $articles,
            'user' => $user,
        ]);
    }

    #[Route('/article/add', name: 'article_add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {


        $article = new Article();
        $article->setAuthor($request->get('author'));
        $article->setText($request->get('text'));

        $entityManager->persist($article);
        $entityManager->flush();

        return $this->json(['article_id' => $article->getId()]);
    }

    #[Route('/article/{id}/delete', name: 'article_delete')]
    public function remove(Article $article, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('app_product');
    }
}