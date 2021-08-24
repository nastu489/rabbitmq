<?php

require dirname(__DIR__) . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;

$host = 'chimpanzee.rmq.cloudamqp.com';
$port = '5672';
$user = 'ryifkfbq';
$pass = '79retxCDzguniRVL3D97AZMWwHRjpD_f';
$vhost = 'ryifkfbq';
$exchange = 'subscribers';
$queue = 'santri_subscribers';

$connection = new AMQPStreamConnection($host, $port, $user, $pass, $vhost);
$channel = $connection->channel();

$channel->queue_declare($queue, false, true, false, false);

$channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);

$channel->queue_bind($queue, $exchange);

function process_message($message){

    $messageBody = json_decode($message->body);
    $email = $messageBody->email;

    file_put_contents(dirname(__DIR__) . '/data/' . $email . '.json', $message->body);
    

    $message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);

}

$consumerTag = 'local.imac.consumer';

$channel->basic_consume($queue, $consumerTag, false, false, false, false, 'process_message');

/**
 * @param \PhpAmqpLib\Channel\AMQPChannel $channel
 * @param \PhpAmqpLib\Connection\AbstractConnection $connection
 */
function shutdown($channel, $connection)
{
    $channel->close();
    $connection->close();
}

register_shutdown_function('shutdown', $channel, $connection);

// Loop as long as the channel has callbacks registered
while ($channel ->is_consuming()) {
    $channel->wait();
}