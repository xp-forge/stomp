STOMP protocol implementation
===

[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.3+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_3plus.png)](http://php.net/)

About
---
STOMP is a network protocol to talk to message brokers such as [Apache ActiveMQ](http://activemq.apache.org/) or [RabbitMQ](http://rabbitmq.org).

The STOMP specification can be found at http://stomp.github.io/.

This implementation requires the XP Framework at least version 5.9.0.

Examples
---

### Producer
A message producer

```php
$conn= new \peer\stomp\Connection(new \peer\URL('stomp://localhost:61613/'));
$conn->connect();

$conn->getDestination('/queue/producer')->send(
  new \peer\stomp\SendableMessage('Message contents', 'text/plain')
);
```

### Consumer
A simple message consumer (subscriber):

```php
$conn= new \peer\stomp\Connection(new \peer\URL('stomp://localhost:61613/'));
$conn->connect();

$sub= $conn->subscribeTo(new \peer\stomp\Subscription('/queue/producer', function($message) {
  Console::writeLine('---> Received message: ', $message);
  $message->ack();
}));

$conn->consume();
```

*For more examples, please see the `examples/` directory.*

### The connection URL
The URL specifies the options how and where to connect:

* `protocol` should be `stomp` or `stomp+ssl` (not implemented yet)
* `host` is the hostname to connect
* `port` is the port to connect (default: 61613)
* `user`, `pass` can be given in the URL and will be used for authentication
* Supported parameters:
  * `log` - pass a log category to log protocol debug output (eg: `?log=default`)
  * `vhost` - virtual host name, since STOMP 1.1 (eg. `?vhost=example.com`)
  * `versions` - to specify list of supported versions (eg. `?versions=1.0,1.1`); default is to support 1.0, 1.1

