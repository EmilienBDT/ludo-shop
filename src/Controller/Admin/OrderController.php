<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\User;
use App\Enum\OrderStatus;
use App\Form\OrderStatusFormType;
use App\Repository\OrderRepository;
use App\Service\OrderStatusService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/orders')]
class OrderController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderStatusService $orderStatusService,
        private readonly OrderRepository $orderRepository,
    ) {
    }

    #[Route('', name: 'app_admin_orders', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $filters = [
            'status' => $request->query->get('status'),
            'dateFrom' => $request->query->get('dateFrom'),
            'dateTo' => $request->query->get('dateTo'),
            'client' => $request->query->get('client'),
            'number' => $request->query->get('number'),
        ];

        $orders = $this->orderRepository->searchAdmin($filters);

        return $this->render('admin/order/index.html.twig', [
            'orders' => $orders,
            'filters' => $filters,
            'statuses' => OrderStatus::cases(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_order_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Order $order): Response
    {
        $allowedStatuses = $this->orderStatusService->allowedTransitions($order);
        $statusForm = $this->createForm(OrderStatusFormType::class, null, ['statuses' => $allowedStatuses]);

        return $this->render('admin/order/show.html.twig', [
            'order' => $order,
            'allowedStatuses' => $allowedStatuses,
            'statusForm' => $statusForm,
        ]);
    }

    #[Route('/{id}/status', name: 'app_admin_order_status', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function updateStatus(Order $order, Request $request): Response
    {
        $allowedStatuses = $this->orderStatusService->allowedTransitions($order);
        $form = $this->createForm(OrderStatusFormType::class, null, ['statuses' => $allowedStatuses]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $status = $form->get('status')->getData();
            if (!$status instanceof OrderStatus) {
                $this->addFlash('danger', 'Statut invalide.');
            } else {
                try {
                    $changedBy = $this->getUser();
                    $this->orderStatusService->transition($order, $status, $changedBy instanceof User ? $changedBy : null);
                    $this->entityManager->flush();
                    $this->addFlash('success', 'Statut mis à jour.');
                } catch (\LogicException $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }
        }

        return $this->redirectToRoute('app_admin_order_show', ['id' => $order->getId()]);
    }

    #[Route('/export', name: 'app_admin_orders_export', methods: ['POST'])]
    public function export(Request $request): Response
    {
        $status = $request->request->get('status');
        $dateFrom = $request->request->get('dateFrom');
        $dateTo = $request->request->get('dateTo');
        $client = $request->request->get('client');
        $number = $request->request->get('number');

        $filters = [
            'status' => is_string($status) ? $status : null,
            'dateFrom' => is_string($dateFrom) ? $dateFrom : null,
            'dateTo' => is_string($dateTo) ? $dateTo : null,
            'client' => is_string($client) ? $client : null,
            'number' => is_string($number) ? $number : null,
        ];

        $orders = $this->orderRepository->searchAdmin($filters);

        $csvContent = "Numéro,Date,Client,Statut,TTC,Pays\n";
        foreach ($orders as $order) {
            $csvContent .= sprintf(
                '"%s","%s","%s","%s","%s","%s"'."\n",
                $order->getNumber(),
                $order->getCreatedAt()->format('d/m/Y H:i'),
                $order->getUser()->getFullName(),
                $order->getStatus()->label(),
                $order->getTotalAmount(),
                $order->getCountry(),
            );
        }

        $response = new Response($csvContent);
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="commandes_'.date('Y-m-d').'.csv"');

        return $response;
    }
}
