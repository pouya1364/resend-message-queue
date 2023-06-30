<?php
declare(strict_types=1);

namespace Rmq;

use Rmq\AWS\QueueService;

class ReplayMessage
{
    public function __construct(private QueueService $queueService)
    {
    }

    public function replay(string $queueName): string
    {
        $queueUrl  = $this->queueService->getSqsUrl($queueName);
        $deadLetterQueue = $this->queueService->getSqsDeadLetterUrl($queueUrl);
        $messages  = $this->queueService->retrieveMessage($deadLetterQueue);

        if (isset($messages)) {
            $this->queueService->sendMessages($queueUrl, $messages);
            $this->queueService->deleteMessage($deadLetterQueue, $messages);
            $result =  "All messages in dead letter queue resend to main queue: $queueName";
        } else {
            $result = "There is no message in queue: $queueName";
        }

        return $result;
    }

}
