# TODO

* ActiveMQ specifics
* SSL
* Should we add `?autoconnect=true`?


## API thoughts

```php
$sub= $conn->subscribe(new Subscription($conn->getDestination('/queue/producer')));

// A
$conn->subscribeTo(new Subscription('a'));
$conn->subscribeTo(new Subscription('b'));

$conn->unsubscribeFrom(new Subscription('b'));

// B
$sub= $conn->getDestination('a')->subscribe();
$conn->getDestination('b')->subscribe(function($m) {
  Console::writeLine('Got ', $m);
});

$conn->getDestination('c');
$dest->send();
$dest->send();
$dest->send();


$sub->unsubscribe();

$conn->receive();
```

