<?php
declare(strict_types=1);

namespace App\Command;

use App\ReplayMessage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:resend-dl-messages')]
class SendDeadLetterQueueCommand extends Command
{
    public function __construct(private ReplayMessage $replayMessage)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Resend Messages in dead letter queues in AWS SQS.')
            ->addArgument(
                'name',
                InputOption::VALUE_REQUIRED,
                'Please give the queue name which has dead letter queue.',
                ''
            );
    }

    protected function execute( InputInterface $input, OutputInterface $output ): int
    {
        $queueName = $input->getArgument('name');
        $result = $this->replayMessage->replay($queueName);

        $output->writeln($result);
        return Command::SUCCESS;
    }
}

