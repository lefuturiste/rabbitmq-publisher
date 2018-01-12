# Rabbit mq publisher

This is a helper for Publish a rabbit mq message.

## Installation

```composer require lefuturiste/rabbitmq-publisher```

## Usage

```php
$rabbitmqClient = new RabbitMQPublisher\Client(
    new AMQPStreamConnection('example.com', '424242', 'user', 'password', 'virtual host')
);
//you can use array as message body
$rabbitmqClient->publish('Hello world', 'my_queue');
```

## Tests

``vendor/bin/phpunit Tests/`` 


Enjoy!