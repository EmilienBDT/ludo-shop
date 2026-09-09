<?php

namespace App\Controller\Admin;

use App\Service\DashboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin')]
class DashboardController extends AbstractController
{
    public function __construct(
        private readonly DashboardService $dashboardService,
    ) {
    }

    #[Route('', name: 'app_admin', methods: ['GET'])]
    public function index(): Response
    {
        $stats = $this->dashboardService->getStats();

        return $this->render('admin/dashboard.html.twig', [
            'totalRevenue' => $stats['totalRevenue'],
            'orderCount' => $stats['orderCount'],
            'topProducts' => $stats['topProducts'],
            'lowStockProducts' => $stats['lowStockProducts'],
        ]);
    }
}
