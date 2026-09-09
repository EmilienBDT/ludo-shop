<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgotPasswordFormType;
use App\Form\RegistrationFormType;
use App\Form\ResetPasswordFormType;
use App\Service\EmailVerificationService;
use App\Service\ResetPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ResetPasswordService $resetPasswordService,
        private readonly EmailVerificationService $emailVerificationService,
    ) {
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (null !== $form->get('website')->getData() && '' !== $form->get('website')->getData()) {
                $this->addFlash('success', 'Votre compte a bien été créé. Vous pouvez vous connecter.');

                return $this->redirectToRoute('app_login');
            }

            if ($form->isValid()) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $form->get('plainPassword')->getData()));

                if ($user->isNewsletterSubscribed()) {
                    $user->setNewsletterToken(bin2hex(random_bytes(32)));
                }

                $this->entityManager->persist($user);
                $this->emailVerificationService->issueToken($user);

                $verificationUrl = $this->generateUrl(
                    'app_verify_email',
                    ['token' => $user->getEmailVerifyToken()],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                );
                $this->emailVerificationService->sendVerificationEmail($user, $verificationUrl);

                $this->addFlash('success', 'Votre compte a bien été créé. Un e-mail de confirmation vient d\'être envoyé à votre adresse.');

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify-email/{token}', name: 'app_verify_email', methods: ['GET'])]
    public function verifyEmail(string $token): Response
    {
        $user = $this->emailVerificationService->findUserByToken($token);
        if (null === $user || $user->isEmailVerified()) {
            $this->addFlash('danger', 'Ce lien de confirmation est invalide ou a déjà été utilisé.');

            return $this->redirectToRoute('app_login');
        }

        $this->emailVerificationService->confirm($user);
        $this->addFlash('success', 'Votre adresse e-mail a été confirmée. Vous pouvez vous connecter.');

        return $this->redirectToRoute('app_login');
    }

    #[Route('/verify-email/resend', name: 'app_verify_email_resend', methods: ['POST'])]
    public function resendVerificationEmail(Request $request): Response
    {
        $email = (string) $request->request->get('email');
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $email,
            'isDeleted' => false,
        ]);

        if (null !== $user && !$user->isEmailVerified()) {
            $this->emailVerificationService->issueToken($user);

            $verificationUrl = $this->generateUrl(
                'app_verify_email',
                ['token' => $user->getEmailVerifyToken()],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );
            $this->emailVerificationService->sendVerificationEmail($user, $verificationUrl);
        }

        $this->addFlash('success', 'Si un compte non confirmé correspond à cette adresse, un nouvel e-mail vient d\'être envoyé.');

        return $this->redirectToRoute('app_login');
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): never
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(Request $request): Response
    {
        $form = $this->createForm(ForgotPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $this->resetPasswordService->requestReset($email);
            if (null !== $user) {
                $resetUrl = $this->generateUrl(
                    'app_reset_password',
                    ['token' => $user->getResetToken()],
                    UrlGeneratorInterface::ABSOLUTE_URL,
                );
                $this->resetPasswordService->sendResetEmail($user, $resetUrl);
            }

            $this->addFlash('success', 'Si un compte existe pour cette adresse, un e-mail de réinitialisation vient d\'être envoyé.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function resetPassword(string $token, Request $request): Response
    {
        $user = $this->resetPasswordService->findUserByToken($token);
        if (null === $user) {
            $this->addFlash('danger', 'Ce lien de réinitialisation est invalide ou expiré.');

            return $this->redirectToRoute('app_forgot_password');
        }

        $form = $this->createForm(ResetPasswordFormType::class, null, ['user' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->resetPasswordService->resetPassword($user, $form->get('plainPassword')->getData());
            $this->addFlash('success', 'Votre mot de passe a été réinitialisé. Vous pouvez vous connecter.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'form' => $form,
            'token' => $token,
        ]);
    }
}
