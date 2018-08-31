<?php
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use RabbitMQPublisher\Client;

class PublisherTest extends TestCase {

	public function getClient()
	{
		return new Client(
			new AMQPStreamConnection('example.com', '424242', 'user', 'password', 'virtual host')
		);
	}

	public function testPublish()
	{
		$client = $this->getClient();
		$response = $client->publish([
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
		], 'example_event');

		$this->assertEquals(true, $response);
	}
}
