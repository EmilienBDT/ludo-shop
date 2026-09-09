<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/categories')]
class CategoryController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('', name: 'app_admin_categories', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/category/index.html.twig', [
            'categories' => $this->entityManager->getRepository(Category::class)->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addFlash('success', 'Catégorie créée.');

            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/category/form.html.twig', [
            'form' => $form,
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_category_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Category $category, Request $request): Response
    {
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Catégorie mise à jour.');

            return $this->redirectToRoute('app_admin_categories');
        }

        return $this->render('admin/category/form.html.twig', [
            'form' => $form,
            'category' => $category,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_category_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Category $category): Response
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();

        $this->addFlash('success', 'Catégorie supprimée.');

        return $this->redirectToRoute('app_admin_categories');
    }
}
