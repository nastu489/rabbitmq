<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

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

$faker = Faker\Factory::create();
$limit = 5000;
$iteration = 0;

while($iteration < $limit){
    $messageBody = json_encode([
        'name' => $faker->name,
        'email' => $faker->email,
        'address' => $faker->address,
        'subscribed' => true,
    ]);
    
    $message = new AMQPMessage($messageBody, [
        'content_type' => 'application/json',
         'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
         ]);
    
    $channel->basic_publish($message, $exchange);

    $iteration++;
}
echo 'Finished publishing to queue: ' . $queue . PHP_EOL;
// $messageBody = implode(' ', array_slice($argv, 1));

$channel->close();
$connection->close();