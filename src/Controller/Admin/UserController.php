<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\ResetPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/users')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ResetPasswordService $resetPasswordService,
    ) {
    }

    #[Route('', name: 'app_admin_users', methods: ['GET'])]
    public function index(): Response
    {
        $users = $this->entityManager->getRepository(User::class)->findBy([], ['createdAt' => 'DESC']);

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/{id}/reset-password', name: 'app_admin_user_reset_password', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function resetPassword(User $user, Request $request): Response
    {
        if (!$this->isCsrfTokenValid('reset_password', $request->request->get('_token') ? (string) $request->request->get('_token') : null)) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        if ($user->isDeleted()) {
            $this->addFlash('danger', 'Impossible d\'envoyer un e-mail à un compte supprimé.');
        } else {
            $resetUser = $this->resetPasswordService->requestReset($user->getEmail());
            if (null === $resetUser) {
                $this->addFlash('danger', 'Impossible d\'envoyer l\'e-mail de réinitialisation.');
            } else {
                $resetUrl = $this->generateUrl(
                    'app_reset_password',
                    ['token' => $resetUser->getResetToken()],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                );
                $this->resetPasswordService->sendResetEmail($resetUser, $resetUrl);
                $this->addFlash('success', sprintf('E-mail de réinitialisation envoyé à %s.', $user->getEmail()));
            }
        }

        return $this->redirectToRoute('app_admin_users');
    }
}
