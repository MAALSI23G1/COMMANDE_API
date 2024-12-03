<?php

namespace App\Domain\Service;

use App\Application\DTO\OrderDTO;
use App\Domain\Entity\Order;
use App\Domain\Entity\OrderLine;
use App\Domain\Repository\ArticleRepositoryInterface;
use App\Domain\Repository\ClearBucketProducerInterface;
use App\Domain\Repository\OrderRepositoryInterface;
use App\Domain\Repository\UpdateQuantityProducerInterface;
use Psr\Log\LoggerInterface;

class CreateOrderService
{
    private OrderRepositoryInterface $orderRepository;
    private ArticleRepositoryInterface $articleRepository;
    private ClearBucketProducerInterface $clearBucketProducer;
    private UpdateQuantityProducerInterface $updateQuantityProducer;
    private LoggerInterface $logger;


    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ArticleRepositoryInterface $articleRepository,
        ClearBucketProducerInterface $clearBucketProducer,
        UpdateQuantityProducerInterface $updateQuantityProducer,
        LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->articleRepository = $articleRepository;
        $this->clearBucketProducer = $clearBucketProducer;
        $this->updateQuantityProducer = $updateQuantityProducer;
        $this->logger = $logger;
    }

    public function execute(OrderDTO $orderDTO): void
    {
        $this->logger->info('Début de l’exécution du cas d’utilisation pour l’utilisateur', ['userId' => $orderDTO->userId]);

        $order = $this->createOrder($orderDTO);

        $this->logger->info('Commande créée avec succès', ['orderId' => $order->getId()]);

        $this->createOrderLines($orderDTO->articles, $order);
        $this->sendMessages();

        $this->logger->info('Fin du cas d’utilisation pour la commande', ['orderId' => $order->getId()]);
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
        try {
            $this->logger->info('Publishing clear bucket message', [
                'userId' => 'AZEAZE',
                'cmd' => 'clear',
            ]);

            $this->clearBucketProducer->publish([
                'userId' => 'AZEAZE',
                'cmd' => 'clear',
            ]);

            $this->logger->info('Publishing update quantity messages', [
                'articles' => [
                    [
                        'articleId' => '12ZEER3',
                        'qteCmd' => 2
                    ],
                    [
                        'articleId' => '12ZECF3',
                        'qteCmd' => 10
                    ]
                ]
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

            $this->logger->info('Messages published successfully');
        } catch (\Exception $e) {
            $this->logger->error('Error while sending messages', [
                'exception' => $e->getMessage()
            ]);
        }
    }
}
