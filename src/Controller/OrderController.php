<?php

namespace App\Controller;

use App\Application\DTO\OrderDTO;
use App\Domain\Service\CreateOrderService;
use App\Domain\Service\GetOrderService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class OrderController
{
    private SerializerInterface $serializer;
    private CreateOrderService $createOrderService;

    private GetOrderService $getOrderService;
    private LoggerInterface $logger;


    public function __construct(SerializerInterface $serializer, GetOrderService $getOrderService, CreateOrderService $createOrderService, LoggerInterface $logger)
    {
        $this->serializer = $serializer;
        $this->getOrderService = $getOrderService;
        $this->createOrderService = $createOrderService;
        $this->logger = $logger;
    }

    #[Route('/test', name: 'test_route', methods: ['GET'])]
    public function test(): JsonResponse
    {
        return new JsonResponse(['message' => 'Test route works!']);
    }

    #[Route('/api/order', name: 'create_order', methods: ['POST'])]
    public function createOrder(Request $request): JsonResponse
    {
        $data = $request->getContent();
        $this->logger->info('Received a request to create an order', ['data' => $data]);

        try {
            $orderDTO = $this->serializer->deserialize($data, OrderDTO::class, 'json');
            $this->logger->info('Deserialization of OrderDTO succeeded', ['orderDTO' => $orderDTO]);

            $this->createOrderService->execute($orderDTO);
            $this->logger->info('Order creation service executed successfully');

            return new JsonResponse(['status' => 'Order created successfully'], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logger->error('Error occurred while creating order', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/orders/{userId}', name: 'get_orders_by_user', methods: ['GET'])]
    public function getOrdersByUserId(int $userId): JsonResponse
    {
        $ordersDTO = $this->getOrderService->getOrdersByUserId($userId);

        if (!$ordersDTO) {
            return new JsonResponse(
                ['error' => 'Order not found'],
                Response::HTTP_NOT_FOUND
            );
        }
        return new JsonResponse($ordersDTO, Response::HTTP_OK);
    }
}
