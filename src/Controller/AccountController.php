<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordType;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class AccountController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/account', name: 'app_account', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getCurrentUser();

        $profileForm = $this->createForm(ProfileType::class, $user);
        $passwordForm = $this->createForm(ChangePasswordType::class);

        return $this->render('account/index.html.twig', [
            'user' => $user,
            'profileForm' => $profileForm,
            'passwordForm' => $passwordForm,
        ]);
    }

    #[Route('/account/profile', name: 'app_account_profile', methods: ['POST'])]
    public function updateProfile(Request $request): Response
    {
        $user = $this->getCurrentUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre profil a été mis à jour.');
        }

        return $this->redirectToRoute('app_account');
    }

    #[Route('/account/password', name: 'app_account_password', methods: ['POST'])]
    public function changePassword(Request $request): Response
    {
        $user = $this->getCurrentUser();
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            if (!$this->passwordHasher->isPasswordValid($user, $data['currentPassword'])) {
                $this->addFlash('danger', 'Le mot de passe actuel est incorrect.');

                return $this->redirectToRoute('app_account');
            }

            $user->setPassword($this->passwordHasher->hashPassword($user, $data['plainPassword']));
            $this->entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été modifié.');
        }

        return $this->redirectToRoute('app_account');
    }

    #[Route('/account/delete', name: 'app_account_delete', methods: ['POST'])]
    public function delete(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('delete_account', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }

        $user = $this->getCurrentUser();
        $user->markAsDeleted();
        $this->entityManager->flush();

        $this->addFlash('success', 'Votre compte a été supprimé.');

        return $this->redirectToRoute('app_logout');
    }

    #[Route('/account/newsletter/toggle', name: 'app_account_newsletter_toggle', methods: ['POST'])]
    public function toggleNewsletter(Request $request): Response
    {
        if (!$this->isCsrfTokenValid('newsletter_toggle', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Jeton de sécurité invalide.');
        }

        $user = $this->getCurrentUser();

        if ($user->isNewsletterSubscribed()) {
            $user->setNewsletterSubscribed(false);
            $user->setNewsletterToken(null);
            $this->addFlash('success', 'Vous êtes désabonné de la newsletter.');
        } else {
            $user->setNewsletterSubscribed(true);
            if (null === $user->getNewsletterToken()) {
                $user->setNewsletterToken(bin2hex(random_bytes(32)));
            }
            $this->addFlash('success', 'Vous êtes abonné à la newsletter.');
        }

        $this->entityManager->flush();

        return $this->redirectToRoute('app_account');
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
