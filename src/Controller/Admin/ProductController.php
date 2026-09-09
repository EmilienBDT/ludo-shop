<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Form\ProductFormType;
use App\Repository\ProductRepository;
use App\Service\StockAlertService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/products')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly StockAlertService $stockAlertService,
    ) {
    }

    #[Route('', name: 'app_admin_products', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('admin/product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($product);
            $this->entityManager->flush();

            $this->addFlash('success', 'Produit créé.');

            return $this->redirectToRoute('app_admin_products');
        }

        return $this->render('admin/product/form.html.twig', [
            'form' => $form,
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_product_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Product $product, Request $request): Response
    {
        $previousStock = $product->getStock();
        $form = $this->createForm(ProductFormType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            if ($previousStock <= 0 && $product->getStock() > 0) {
                $this->stockAlertService->process();
            }

            $this->addFlash('success', 'Produit mis à jour.');

            return $this->redirectToRoute('app_admin_products');
        }

        return $this->render('admin/product/form.html.twig', [
            'form' => $form,
            'product' => $product,
        ]);
    }

    #[Route('/{id}/toggle', name: 'app_admin_product_toggle', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function toggle(Product $product): Response
    {
        $product->setIsActive(!$product->isActive());
        $this->entityManager->flush();

        $this->addFlash(
            'success',
            $product->isActive() ? 'Produit activé (visible dans le catalogue).' : 'Produit désactivé (masqué du catalogue).',
        );

        return $this->redirectToRoute('app_admin_products');
    }
}
