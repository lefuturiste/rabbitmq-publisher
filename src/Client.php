<?php

namespace RabbitMQPublisher;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Client
{
    /**
     * @var string
     */
    private $type = 'direct';

    /**
     * @var string
     */
    private $exchange = 'amq.direct';

    /**
	 * @var AMQPStreamConnection
	 */
	public $connexion;

    /**
     * @var AMQPChannel
     */
	private $channel;

    /**
     * @var string
     */
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
     * @param string $event
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
			json_encode([
			    'event' => $event,
                'body' => $body
            ]),
			[
				'content_type' => 'application/json',
				'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
			]
		);
		$this->channel->basic_publish($message, $this->exchange);
		$this->shutdown();

		return true;
	}

	private function shutdown(): void
	{
		$this->channel->close();
		$this->connexion->close();
	}
}
