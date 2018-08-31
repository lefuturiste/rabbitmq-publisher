# Rabbit mq publisher

This is a helper for Publish a rabbit mq message.

## Installation

```composer require lefuturiste/rabbitmq-publisher```

## Usage

```php
$rabbitmqClient = new Lefuturiste\RabbitMQPublisher\Client(
    new AMQPStreamConnection('example.com', '424242', 'user', 'password', 'virtual_host')
);
//you can use array as message body
//body is json encoded
$rabbitmqClient->publish('Hello world', 'my_event');
```

## Tests

``vendor/bin/phpunit Tests/``


Enjoy!
