<?php

namespace App\Domain\Service;

use App\Application\DTO\OrderDTO;
use App\Domain\Entity\Order;
use App\Domain\Entity\OrderLine;
use App\Domain\Repository\ArticleRepositoryInterface;
use App\Domain\Repository\ClearBucketProducerInterface;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\UpdateQuantityProducerInterface;

class CreateOrderService
{
    private OrderRepositoryInterface $orderRepository;
    private ArticleRepositoryInterface $articleRepository;
    private ClearBucketProducerInterface $clearBucketProducer;
    private UpdateQuantityProducerInterface $updateQuantityProducer;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ArticleRepositoryInterface $articleRepository,
        ClearBucketProducerInterface $clearBucketProducer,
        UpdateQuantityProducerInterface $updateQuantityProducer,
    ) {
        $this->orderRepository = $orderRepository;
        $this->articleRepository = $articleRepository;
        $this->clearBucketProducer = $clearBucketProducer;
        $this->updateQuantityProducer = $updateQuantityProducer;
    }

    public function execute(OrderDTO $orderDTO): void
    {
        $order = $this->createOrder($orderDTO);
        $this->createOrderLines($orderDTO->articles, $order);
        $this->sendMessages();
    }

    private function createOrder(OrderDTO $orderDTO): Order
    {
        $order = new Order();
        $order->setUserId($orderDTO->userId);
        $this->orderRepository->save($order);
        return $order;
    }

    private function createOrderLines(array $articles, Order $order): void
    {
        foreach ($articles as $articleDTO) {
            $article = new OrderLine();
            $article->setArticleId($articleDTO->articleId);
            $article->setName($articleDTO->name);
            $article->setQuantity($articleDTO->quantity);
            $article->setPrice($articleDTO->price);
            $article->setOrder($order);
            $this->articleRepository->save($article);
        }
    }

    private function sendMessages(): void
    {
        $this->clearBucketProducer->publish([
            'userId' => 'AZEAZE',
            'cmd' => 'clear',
        ]);

        $this->updateQuantityProducer->publish([
            [
                'articleId' => '12ZEER3',
                'qteCmd' => 2
            ],
            [
                'articleId' => '12ZECF3',
                'qteCmd' => 10
            ]
        ]);
    }
}
