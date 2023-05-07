<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
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
    #[Route('/aticle', name: 'app_article')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findAll();

        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
            'articles' => $articles,
        ]);
    }

    #[Route('/article/add', name: 'article_add')]
    public function add() : Response
    {
        return $this->render('article/index.html.twig', [
           'controller_name' => 'ArticleController',
           'test_string' =>  'ARTICLE ADD',
        ]);
    }

    #[Route('/article/{id}/delete', name: 'article_delete')]
    public function remove(Article $article, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($article);
        $entityManager->flush();

        return $this->redirectToRoute('app_product');
    }
}