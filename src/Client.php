<?php

namespace Lefuturiste\RabbitMQPublisher;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Client
{
    /*
     * AMQPStreamConnection connexions config (host, port, user, password, virtual host)
     */
    private $host;
    private $port;
    private $user;
    private $password;
    private $virtualHost;

    /**
     * @var AMQPStreamConnection|null
     */
    private $connexion = NULL;

    /**
     * @var AMQPChannel|null
     */
    private $channel = NULL;

    /**
     * @var string
     */
    private $queue;

    /**
     * @var string
     */
    private $type = 'direct';

    /**
     * @var string
     */
    private $exchange = 'amq.direct';

    /**
     * @var int
     */
    private $deliveryMode = AMQPMessage::DELIVERY_MODE_PERSISTENT;

    /**
     * Flag to define the mode of the client
     * If this flag is set to true we will not actually send the messages to the broker,
     * but we will store on the $messages propriety
     *
     * @var bool
     */
    private $unitTestMode;

    /**
     * The messages to send
     *
     * @var array
     */
    private $messages;

    /**
     * @var \Exception
     */
    private $connexionException = NULL;

    public function __construct(
        $host,
        $port,
        $user,
        $password,
        $virtualHost,
        $queue = 'default',
        $unitTestMode = false
    )
    {
        $this->queue = $queue;
        $this->unitTestMode = $unitTestMode;
        $this->host = $host;
        $this->virtualHost = $virtualHost;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Create a actual TCP/IP connection to the Rabbit MQ server using AMQPStreamConnection
     * return true if the connexion was successful else return false
     *
     * @return bool
     */
    public function connect(): bool
    {
        try {
            $this->connexion = new AMQPStreamConnection(
                $this->host,
                $this->port,
                $this->user,
                $this->password,
                $this->virtualHost
            );
            return true;
        } catch (\Exception $e) {
            // can't connect because of an exception (can be bad config, or invalid socket...)
            $this->connexionException = $e;
            return false;
        }
    }

    /**
     * Publish a message with a specific event in the default queue
     * return true if the publication was successful else return false
     * for a failed socket connexion using connect() for example
     *
     * @param mixed $body
     * @param string $event
     * @return boolean
     */
    public function publish($body, string $event): bool
    {
        $queue = $this->queue;
        $messageRow = json_encode([
            'event' => $event,
            'body' => $body
        ]);

        if ($this->unitTestMode) {
            $this->messages[] = $messageRow;
            return true;
        }

        if (!$this->hasConnexion() && !$this->connect()) {
            return false;
        }
        $this->channel = $this->connexion->channel();
        $this->channel->queue_declare($queue, false, true, false, false);
        $this->channel->exchange_declare($this->exchange, $this->type, false, true, false);
        $this->channel->queue_bind($queue, $this->exchange);
        $message = new AMQPMessage(
            $messageRow,
            [
                'content_type' => 'application/json',
                'delivery_mode' => $this->deliveryMode
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

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function hasMessages(): bool
    {
        return !empty($this->messages);
    }

    public function getMessagesCount(): int
    {
        return count($this->messages);
    }

    public function clearMessages(): void
    {
        $this->messages = [];
    }

    public function setUnitTestMode(bool $unitTestMode): void
    {
        $this->unitTestMode = $unitTestMode;
    }

    public function isInUnitTestMode(): bool
    {
        return $this->unitTestMode;
    }

    public function getQueue(): string
    {
        return $this->queue;
    }

    public function hasConnexion(): bool
    {
        return $this->connexion !== NULL;
    }

    public function isOnline(): bool
    {
        return true;
    }

    /**
     * @return AMQPStreamConnection|null
     */
    public function getConnexion()
    {
        return $this->connexion;
    }

    /**
     * @return \Exception|null
     */
    public function getConnexionException()
    {
        return $this->connexionException;
    }
}
