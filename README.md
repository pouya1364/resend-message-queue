# resend-message-queue
This is a Command line application to send messages from a dead letter queue in AWS SQS.

* This is a PHP application to work with AWS SQS to reply to the messages in the dead letter queue to the main queue, therefore consumers of the main queue could start to consume the message.
In some scenario, the message could not consume for any error and after fixing the error message need to be replied to the main one.




#### Install
* you need to install PHP and composer in your Docker, VM,pc and etc...
* You need to run ```composer install``` to install packages
* You need to set the below variables in ```.env``` file:
          
          AWS_ACCESS_KEY_ID=yourkey
          AWS_SECRET_ACCESS_KEY=yoursecret
          AWS_REGION=eu-central-1
          AWS_VERSION=latest
          AWS_ENDPOINT=https://sqs.eu-central-1.amazonaws.com
          
### Command
 ``` bin/console rmq:resend-dl-messages MAIN-QUEUE-NAME```
### Sample
 ``` bin/console rmq:resend-dl-messages my-queue```
