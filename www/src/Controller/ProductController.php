<?php

namespace App\Controller;

use App\Service\AuthService;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $products = $entityManager->getRepository(Product::class)->findAll();

        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'products' => $products,
        ]);
    }

    #[Route('/product/add', name: 'product_add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $product->setName($request->get('name'));
        $product->setPrice($request->get('price'));
        $product->setSize($request->get('size'));
        $entityManager->persist($product);
        $entityManager->flush();


        return $this->json(['product_id' => $product->getId()]);
    }
}
