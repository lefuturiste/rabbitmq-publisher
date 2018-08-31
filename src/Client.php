<?php

namespace Lefuturiste\RabbitMQPublisher;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Client
{
	/**
	 * @var AMQPStreamConnection
	 */
	public $connexion;
	private $type = 'direct';
	private $exchange = 'amq.direct';
	private $channel;
	private $queue;

	public function __construct(AMQPStreamConnection $connexion, $queue = 'default')
	{
		$this->connexion = $connexion;
		$this->queue = $queue;
	}

	/**
	 * Publish a message with a specific event in the default queue
	 *
	 * @param mixed $body
	 * @param string $queue
	 *
	 * @return boolean
	 */
	public function publish($body, string $event)
	{
	    $queue = $this->queue;
		$this->channel = $this->connexion->channel();
		$this->channel->queue_declare($queue, false, true, false, false);
		$this->channel->exchange_declare($this->exchange, $this->type, false, true, false);
		$this->channel->queue_bind($queue, $this->exchange);
		$message = new AMQPMessage(
			json_encode(['event' => $event, 'body' => $body]),
			[
				'content_type' => 'application/json',
				'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
			]
		);
		$this->channel->basic_publish($message, $this->exchange);
		$this->shutdown();

		return true;
	}

	/**
	 *
	 */
	private function shutdown()
	{
		$this->channel->close();
		$this->connexion->close();
	}
}
