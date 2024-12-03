<?php

namespace App\Domain\Service;

use App\Domain\Repository\OrderRepositoryInterface;

class GetOrderService
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getOrdersByUserId(int $userId): array
    {
        return $this->orderRepository->findByUserId($userId);
    }
}
