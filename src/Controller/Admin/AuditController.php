<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Repository\AuditLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/audit')]
class AuditController extends AbstractController
{
    #[Route('', name: 'app_admin_audit', methods: ['GET'])]
    public function index(AuditLogRepository $auditLogRepository): Response
    {
        $entries = $auditLogRepository->findBy([], ['createdAt' => 'DESC'], 200);

        return $this->render('admin/audit/index.html.twig', [
            'entries' => $entries,
        ]);
    }
}
