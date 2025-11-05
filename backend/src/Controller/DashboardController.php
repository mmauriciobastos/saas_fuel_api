<?php

namespace App\Controller;

use App\Repository\ClientRepository;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    public function __construct(
        private readonly OrderRepository $orderRepository,
        private readonly ClientRepository $clientRepository,
    ) {
    }

    #[Route('/api/dashboard', name: 'api_dashboard', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        // All queries are automatically filtered by company via Doctrine's company_filter
        
        // total liters across all orders (scoped by Doctrine company_filter)
        $totalLiters = (float) $this->orderRepository->createQueryBuilder('o')
            ->select('COALESCE(SUM(o.fuelAmount), 0)')
            ->getQuery()
            ->getSingleScalarResult();

        // counts by status
        $delivered = (int) $this->orderRepository->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.status = :s')
            ->setParameter('s', 'delivered')
            ->getQuery()
            ->getSingleScalarResult();

        $pending = (int) $this->orderRepository->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.status = :s')
            ->setParameter('s', 'pending')
            ->getQuery()
            ->getSingleScalarResult();

        $scheduled = (int) $this->orderRepository->createQueryBuilder('o')
            ->select('COUNT(o.id)')
            ->andWhere('o.status = :s')
            ->setParameter('s', 'scheduled')
            ->getQuery()
            ->getSingleScalarResult();

        // total customers
        $totalCustomers = (int) $this->clientRepository->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // liters by month for delivered orders
        // Strategy: find min/max deliveredAt for delivered orders, then iterate month-by-month
        $rangeRow = $this->orderRepository->createQueryBuilder('o')
            ->select('MIN(o.deliveredAt) AS minDate, MAX(o.deliveredAt) AS maxDate')
            ->andWhere('o.status = :delivered')
            ->setParameter('delivered', 'delivered')
            ->getQuery()
            ->getOneOrNullResult();

        $litersByMonth = [];
        if ($rangeRow && $rangeRow['minDate'] && $rangeRow['maxDate']) {
            $min = $rangeRow['minDate'];
            $max = $rangeRow['maxDate'];
            
            // Normalize to DateTimeImmutable
            if (!$min instanceof \DateTimeInterface) {
                $min = new \DateTimeImmutable((string) $min);
            }
            if (!$max instanceof \DateTimeInterface) {
                $max = new \DateTimeImmutable((string) $max);
            }

            $cursor = new \DateTimeImmutable($min->format('Y-m-01 00:00:00'));
            $limit = (new \DateTimeImmutable($max->format('Y-m-01 00:00:00')))->modify('+1 month');

            while ($cursor < $limit) {
                $next = $cursor->modify('+1 month');

                $sum = $this->orderRepository->createQueryBuilder('o')
                    ->select('COALESCE(SUM(o.fuelAmount), 0)')
                    ->andWhere('o.status = :delivered')
                    ->andWhere('o.deliveredAt >= :from')
                    ->andWhere('o.deliveredAt < :to')
                    ->setParameter('delivered', 'delivered')
                    ->setParameter('from', $cursor)
                    ->setParameter('to', $next)
                    ->getQuery()
                    ->getSingleScalarResult();

                $sumFloat = (float) $sum;
                // Always include the month, even if zero liters
                $litersByMonth[] = [
                    'month' => $cursor->format('Y-m'),
                    'totalLiters' => $sumFloat,
                ];

                $cursor = $next;
            }
        }

        return $this->json([
            'totalLiters' => $totalLiters,
            'totalCustomers' => $totalCustomers,
            'deliveredOrders' => $delivered,
            'pendingOrders' => $pending,
            'scheduledOrders' => $scheduled,
            'litersByMonth' => $litersByMonth,
        ]);
    }
}
