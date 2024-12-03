<?php

namespace App\Infrastructure\Messaging\Catalogue;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateQuantityMessageHandler extends Command
{
    private UpdateQuantityProducer $producer;

    public function __construct(UpdateQuantityProducer $producer)
    {
        $this->producer = $producer;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $message = [
            [
                'articleId' => '12ZEER3',
                'qteCmd' => 2
            ],
            [
                'articleId' => '12ZECF3',
                'qteCmd' => 10
            ]
        ];

        $this->producer->publish($message);

        return Command::SUCCESS;
    }
}
