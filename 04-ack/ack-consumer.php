<?php declare(strict_types = 1);

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest', '/');
$channel = $connection->channel();

$channel->queue_declare('emails', false, true, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$channel->basic_consume('emails', '', false, false, false, false, function (AMQPMessage $message) {
    $data = $message->getBody();
    echo 'Processing message: ' . $data . "\n";
    sleep(30);

    /** @var \PhpAmqpLib\Channel\AMQPChannel $channel */
    $channel = $message->delivery_info['channel'];
    $channel->basic_ack($message->delivery_info['delivery_tag']);

    echo 'Processed!' . "\n";
});

while (count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();
