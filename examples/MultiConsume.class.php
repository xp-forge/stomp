<?php

use org\codehaus\stomp\Connection;
use org\codehaus\stomp\Subscription;
use org\codehaus\stomp\AckMode;
use util\log\Logger;
use util\log\LogCategory;
use util\log\ColoredConsoleAppender;

class MultiConsume extends \util\cmd\Command {

  public function run() {
    // Logger::getInstance()->getCategory()->withAppender(new ColoredConsoleAppender());
    $conn= new Connection(new \peer\URL('stomp://localhost:61613/?log=default'));

    $conn->connect();

    $sub1= $conn->subscribeTo(new Subscription('/queue/producer'));
    $sub2= $conn->subscribeTo(new Subscription('/queue/foobar', AckMode::AUTO));

    // $sub= $conn->subscribe(new Subscription($conn->getDestination('/queue/producer')));

    // // A
    // $conn->subscribeTo(new Subscription('a'));
    // $conn->subscribeTo(new Subscription('b'));

    // $conn->unsubscribeFrom(new Subscription('b'));

    // // B
    // $sub= $conn->getDestination('a')->subscribe();
    // $conn->getDestination('b')->subscribe(function($m) {
    //   Console::writeLine('Got ', $m);
    // });


    do {
      $msg= $conn->receive(100);
      // $this->out->writeLine('Consuming: ', \xp::stringOf($msg));

      if ($msg) {
        if ($msg->ackable()) {
          $this->out->writeLine('Acking message ', $msg->getMessageId());
          $msg->ack();
        } else {
          $this->out->writeLine('Leaving message ', $msg->getMessageId(), ' - it is not ackable.');
        }
      }
    } while ($msg instanceof \org\codehaus\stomp\ReceivedMessage);

    // $conn->getDestination('c');
    // $dest->send();
    // $dest->send();
    // $dest->send();


    // $sub->unsubscribe();

    // $conn->receive();
  }
}