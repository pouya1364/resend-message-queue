<?php
declare(strict_types=1);

namespace App\AWS;

use Aws\Sqs\SqsClient;

class QueueService
{
    public function __construct(private SqsClient $client)
    {
    }

    /**
     * Get messages from an AWS SQS queue.
     */
    public function retrieveMessage(string $queueUrl): array|null
    {
        $result = $this->client->receiveMessage([
            'QueueUrl'              => $queueUrl,
            'AttributeNames'        => ['SentTimestamp'],
            'MessageAttributeNames' => ['All'],
        ]);


      return   $result->get('Messages');

    }

    /**
     * Send messages to an AWS SQS queue.
     */
    public function sendMessages(string $queueUrl, array $messages): void
    {
        $entries = array_map(
            static function (array $message): array {
                return [
                    'MessageBody'       => $message['Body'],
                    'Id'                => $message['MessageId'],
                    'MessageAttributes' =>
                        array_merge(
                            $message['MessageAttributes'] ?? [],
                            [
                                'retryAt'        => [
                                    'DataType'    => 'String',
                                    'StringValue' => (new \DateTimeImmutable())->format(DATE_ATOM),
                                ],
                                'retryTimestamp' => [
                                    'DataType'    => 'Number',
                                    'StringValue' => time(),
                                ],
                            ],
                        ),
                ];
            },
            $messages
        );

        $sqsMessages = [
            'Entries'  => $entries,
            'QueueUrl' => $queueUrl,
        ];

        $this->client->sendMessageBatch($sqsMessages);
    }


    /**
     * Delete messages from an AWS SQS queue.
     */
    public function deleteMessage(string $queueUrl, array $messages): void
    {
        $entries = array_map(
            static function (array $message): array {
                return [
                    'ReceiptHandle' => $message['ReceiptHandle'],
                    'Id'            => $message['MessageId'],
                ];
            },
            $messages
        );

        $sqsMessages = [
            'Entries'  => $entries,
            'QueueUrl' => $queueUrl,
        ];

        $this->client->deleteMessageBatch($sqsMessages);
    }

    /**
     * Get the dead letter queue url for an AWS SQS queue.
     */
    public function getSqsDeadLetterUrl(string $queueUrl): string|null
    {
        $result = $this->client->getQueueAttributes(
            [
                'QueueUrl'       => $queueUrl,
                'AttributeNames' => ['RedrivePolicy'],
            ]
        );
        $attributes        = $result->get('Attributes');
        $redrivePolicyJson = $attributes['RedrivePolicy'];
        if (!$redrivePolicyJson) {
            throw new \InvalidArgumentException("'$queueUrl' does not have dead letter queue.");
        }

        $redrivePolicy         = \json_decode($redrivePolicyJson, true, 512, JSON_THROW_ON_ERROR);
        $deadLetterTargetArn   = $redrivePolicy['deadLetterTargetArn'];
        $deadLetterTargetParts = explode(':', $deadLetterTargetArn);
        $deadLetterQueue       =  end( $deadLetterTargetParts);

        return $this->getSqsUrl( $deadLetterQueue);
    }


    /**
     * Get the url for an AWS SQS queue.
     */
    public function getSqsUrl(string $queueName): string|null
    {
        try {
            $result = $this->client->getQueueUrl(
                [
                    'QueueName' => $queueName,
                ]
            );

            return $result->get('QueueUrl');
        } catch (\Exception $e) {
            throw new \RuntimeException(
                sprintf('Could not get queue url for queue "%s"', $queueName),
            0,
                $e->getMessage());
        }
    }

}