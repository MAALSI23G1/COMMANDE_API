<?php

namespace App\Infrastructure\Messaging\Bucket;

use App\Infrastructure\Messaging\Bucket;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearBucketMessageHandler extends Command
{
    private Bucket\ClearBucketProducer $producer;

    public function __construct(ClearBucketProducer $producer)
    {
        $this->producer = $producer;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = [
            'userId' => 'AZEAZE',
            'cmd' => 'clear',
        ];

        $this->producer->publish($message);

        return Command::SUCCESS;
    }
}
