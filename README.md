STOMP protocol implementation
===

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-forge/stomp.svg)](http://travis-ci.org/xp-forge/stomp)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.5+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_5plus.png)](http://php.net/)
[![Supports PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-forge/stomp/version.png)](https://packagist.org/packages/xp-forge/stomp)

About
---
STOMP is a network protocol to talk to message brokers such as [Apache ActiveMQ](http://activemq.apache.org/) or [RabbitMQ](http://rabbitmq.org).

The STOMP specification can be found at http://stomp.github.io/.

Examples
---

### Producer
A message producer

```php
use peer\stomp\Connection;
use peer\stomp\SendableMessage;
use peer\URL;

$conn= new Connection(new URL('stomp://localhost:61613/'));
$conn->connect();

$conn->getDestination('/queue/producer')->send(
  new SendableMessage('Message contents', 'text/plain')
);
```

### Consumer
A simple message consumer (subscriber):

```php
use peer\stomp\Connection;
use peer\stomp\Subscription;
use peer\URL;

$conn= new Connection(new URL('stomp://localhost:61613/'));
$conn->connect();

$sub= $conn->subscribeTo(new Subscription('/queue/producer', function($message) {
  Console::writeLine('---> Received message: ', $message);
  $message->ack();
}));

$conn->consume();
```

### Multi-endpoint failover
A consumer with a broker network may connect to any host when available:

```php
use peer\stomp\Connection;
use peer\stomp\Subscription;
use peer\stomp\Failover;
use peer\URL;

$conn= new Connection(Failover::using([
  new URL('stomp://one.example.com:61613/'),
  new URL('stomp://two.example.com:61613/')
])->byRandom());

// Connect randomly to one or the other
$conn->connect();

$sub= $conn->subscribeTo(new Subscription('/queue/producer', function($message) {
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

