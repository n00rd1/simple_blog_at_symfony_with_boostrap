<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;

// Для работы с HTTP кодом
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

// Для хеширования пароля
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class CommentController extends AbstractController
{
    #[Route('/comment', name: 'app_comment')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $comments = $entityManager->getRepository(Comments::class)->findAll();

        return $this->render('comment/com_list.html.twig', [
            'controller_name' => 'CommentController',
            'comments' => $comments,
        ]);
    }

    #[Route('/comment/create', name: 'comment_create')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        // TODO валидация входных данных

        $comment = new Comment();
        $comment->setComment($request->get('comment'));
        $comment->setAuthor($request->get('author'));
        $comment->setArticle($request->get('article'));
        $entityManager->persist($comment);
        $entityManager->flush();


        return $this->json(['comment_id' => $comment->getId()]);

/*        return $this->render('comment/com_list.html.twig', [
            'controller_name' => 'CommentController',
            'comment_string' => 'COMMENT ADD',
        ]);*/
    }

    #[Route('/comment/{id}/delete', name: 'comment_delete')]
    public function remove(Comment $comment, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirect('app_product');
    }
}