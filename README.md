STOMP protocol implementation
===

About
---
STOMP is a network protocol to talk to message brokers such as [Apache ActiveMQ](http://activemq.apache.org/) or [RabbitMQ](http://rabbitmq.org).

This implementation requires the XP Framework at least version 5.9.0.

Examples
---

### Producer
A message producer

```php
$conn= new Connection(new \peer\URL('stomp://localhost:61613/'));
$conn->connect();

$msg= new SendableMessage('Message contents', 'text/plain');
$msg->send($conn->getDestination('/queue/producer'));
```

### Consumer
A simple message consumer (subscriber):

```php
$conn= new Connection(new \peer\URL('stomp://localhost:61613/'));
$conn->connect();

$sub= $conn->subscribeTo(new Subscription('/queue/producer'));
$msg= $conn->receive();
```

### The connection URL
The URL specifies the options how and where to connect:

* `protocol` should be `stomp` or `stomp+ssl` (not implemented yet)
* `host` is the hostname to connect
* `port`
