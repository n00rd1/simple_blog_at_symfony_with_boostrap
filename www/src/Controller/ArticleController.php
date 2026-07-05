<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\User;
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
    public function index(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findBy([], ['createdAt' => 'DESC']);

        return $this->render('article/article_list.html.twig', [
            'controller_name' => 'ArticleController',
            'articles' => $articles,
            'user' => $this->getUser(),
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
