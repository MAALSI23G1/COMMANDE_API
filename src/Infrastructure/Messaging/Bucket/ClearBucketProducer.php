<?php

namespace App\Infrastructure\Messaging\Bucket;

use App\Domain\Repository\ClearBucketProducerInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ClearBucketProducer implements ClearBucketProducerInterface
{
    private AMQPStreamConnection $connection;
    private string $exchange;
    private string $queue;

    public function __construct(AMQPStreamConnection $connection, string $exchange, string $queue)
    {
        $this->connection = $connection;
        $this->exchange = $exchange;
        $this->queue = $queue;
    }

    public function publish(array $messageData): void
    {

        $channel = $this->connection->channel();

        $channel->exchange_declare($this->exchange, 'direct', false, true, false);
        $channel->queue_declare($this->queue, false, true, false, false);
        $channel->queue_bind($this->queue, $this->exchange, $this->queue);

        $message = new AMQPMessage(json_encode($messageData));

        try {
            $channel->basic_publish($message, $this->exchange, $this->queue);
            $channel->close();
        } catch (\Exception $e) {
            error_log("Erreur lors de l'envoi du message : " . $e->getMessage());
        }
    }
}
