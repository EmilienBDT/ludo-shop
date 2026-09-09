<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository): Response
    {
        $user = $this->getUser();
        $includeMature = $user instanceof User && $user->isAdult();

        return $this->render('home/index.html.twig', [
            'products' => array_slice($productRepository->findActive($includeMature), 0, 6),
        ]);
    }
}
