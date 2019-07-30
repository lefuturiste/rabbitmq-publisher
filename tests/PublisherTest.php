<?php

use PHPUnit\Framework\TestCase;
use Lefuturiste\RabbitMQPublisher\Client;

class PublisherTest extends TestCase {

    public $eventName = 'example_event';
    public $eventBody = [
        'subject' => 'Test!',
        'altBody' => 'An Alt Body',
        'body' => 'An Email body <b>Bold!</b> and <i>Italic</i> and <br> break line !! <hr> Horizontal rule <ul> <li>Bullet point 1</li></ul>',
        'to' => [
            [
                'username' => 'Mon beau miroir',
                'address' => 'spamfree@matthieubessat.fr'
            ]
        ],
        'from' => 'Example from name',
        'isHTML' => true
    ];

    public static function setUpBeforeClass()
    {
        \Dotenv\Dotenv::create(dirname(__DIR__))->load();
    }

    private function getClient(): Client
	{
		return new Client(
            getenv('RABBITMQ_HOST'),
            getenv('RABBITMQ_PORT'),
            getenv('RABBITMQ_USER'),
            getenv('RABBITMQ_PASSWORD'),
            getenv('RABBITMQ_VIRTUAL_HOST')
		);
	}

	private function publish(Client $client): bool
    {
        return $client->publish($this->eventBody, $this->eventName);
    }

	public function testPublish()
	{
		$client = $this->getClient();
        $result = $this->publish($client);
		$this->assertEquals(true, $result);
        $this->assertFalse($client->hasMessages());
	}

    public function testPublishUnitTest()
    {
        $client = $this->getClient();
        $this->assertFalse($client->hasConnexion());
        $client->setUnitTestMode(true);
        $result = $this->publish($client);
        $this->assertTrue($client->hasMessages());
        $this->assertCount(1, $client->getMessages());
        $this->assertEquals(1, $client->getMessagesCount());
        $body = json_decode($client->getMessages()[0], true);
        $this->assertEquals($this->eventName, $body['event']);
        $this->assertEquals($this->eventBody, $body['body']);
        $client->clearMessages();
        $this->assertFalse($client->hasMessages());
        $this->assertEquals(0, $client->getMessagesCount());
        $this->assertCount(0, $client->getMessages());
        $this->assertEmpty($client->getMessages());
        $this->assertEquals(true, $result);
    }
}
