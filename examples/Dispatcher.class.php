<?php

use org\codehaus\stomp\Connection;
use org\codehaus\stomp\Subscription;
use util\log\Logger;
use util\log\LogCategory;
use util\log\ColoredConsoleAppender;

class Dispatcher extends \util\cmd\Command {

  public function run() {
    // Logger::getInstance()->getCategory()->withAppender(new ColoredConsoleAppender());
    $conn= new Connection(new \peer\URL('stomp://localhost:61613/?log=default'));
    $conn->connect();

    $sub= $conn->subscribeTo(new Subscription('/queue/producer'));

    do {
      $msg= $conn->receive(100);
      $this->out->writeLine('Consuming: ', \xp::stringOf($msg));

      $cpy= $msg->toSendable();
      $cpy->send($conn->getDestination('/queue/foobar'));

      if ($msg) {
        $msg->ack();
      }
    } while ($msg instanceof \org\codehaus\stomp\ReceivedMessage);
  }
}
