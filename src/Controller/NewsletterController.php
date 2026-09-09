<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NewsletterController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/newsletter/unsubscribe/{token}', name: 'app_newsletter_unsubscribe', methods: ['GET'])]
    public function unsubscribe(string $token): Response
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'newsletterToken' => $token,
            'isDeleted' => false,
        ]);

        if (null === $user || !$user->isNewsletterSubscribed()) {
            $this->addFlash('danger', 'Ce lien de désinscription est invalide ou a déjà été utilisé.');

            return $this->redirectToRoute('app_home');
        }

        $user->setNewsletterSubscribed(false);
        $user->setNewsletterToken(null);
        $this->entityManager->flush();

        return $this->render('newsletter/unsubscribed.html.twig');
    }
}
