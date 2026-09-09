<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Address;
use App\Entity\User;
use App\Form\AddressFormType;
use App\Service\AddressService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
#[Route('/account/addresses')]
class AddressController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AddressService $addressService,
    ) {
    }

    #[Route('', name: 'app_address_index', methods: ['GET'])]
    public function index(): Response
    {
        $addresses = $this->addressService->findByUser($this->getCurrentUser());

        return $this->render('account/address/index.html.twig', [
            'addresses' => $addresses,
        ]);
    }

    #[Route('/new', name: 'app_address_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $address = new Address(
            user: $this->getCurrentUser(),
            label: '',
            addressLine: '',
            city: '',
            postalCode: '',
            country: 'FR',
        );

        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addressService->setDefaultIfNeeded($address);
            $this->entityManager->persist($address);
            $this->entityManager->flush();

            $this->addFlash('success', 'Adresse créée.');

            return $this->redirectToRoute('app_address_index');
        }

        return $this->render('account/address/form.html.twig', [
            'form' => $form,
            'isNew' => true,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_address_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Address $address): Response
    {
        $this->assertOwnership($address);

        $form = $this->createForm(AddressFormType::class, $address);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addressService->setDefaultIfNeeded($address);
            $this->entityManager->persist($address);
            $this->entityManager->flush();

            $this->addFlash('success', 'Adresse mise à jour.');

            return $this->redirectToRoute('app_address_index');
        }

        return $this->render('account/address/form.html.twig', [
            'form' => $form,
            'isNew' => false,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_address_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Request $request, Address $address): Response
    {
        $this->assertOwnership($address);

        $this->entityManager->remove($address);
        $this->entityManager->flush();

        $this->addFlash('success', 'Adresse supprimée.');

        return $this->redirectToRoute('app_address_index');
    }

    #[Route('/{id}/default', name: 'app_address_default', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function setDefault(Address $address): Response
    {
        $this->assertOwnership($address);

        $this->addressService->setDefault($address);

        $this->addFlash('success', 'Adresse par défaut mise à jour.');

        return $this->redirectToRoute('app_address_index');
    }

    private function assertOwnership(Address $address): void
    {
        if ($address->getUser()->getId() !== $this->getCurrentUser()->getId()) {
            throw $this->createAccessDeniedException();
        }
    }

    private function getCurrentUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        return $user;
    }
}
