<?php namespace examples;

use org\codehaus\stomp\Connection;
use org\codehaus\stomp\Subscription;
use util\log\Logger;
use util\log\LogCategory;
use util\log\ColoredConsoleAppender;

class Dispatcher extends \util\cmd\Command {

  #[@arg]
  public function setDebug($d= FALSE) {
    if (NULL === $d) {
      Logger::getInstance()->getCategory()->withAppender(new ColoredConsoleAppender());
    }
  }

  public function run() {
    // Logger::getInstance()->getCategory()->withAppender(new ColoredConsoleAppender());
    $conn= new Connection(new \peer\URL('stomp://localhost:61613/?log=default'));
    $conn->connect();

    $sub= $conn->subscribeTo(new Subscription('/queue/producer'));
    $dest= $conn->getDestination('/queue/foobar');

    do {
      $msg= $conn->receive(100);
      $this->out->writeLine('Consuming: ', \xp::stringOf($msg));

      $cpy= $msg->toSendable();
      $dest->send($cpy);

      if ($msg) {
        $msg->ack();
      }
    } while ($msg instanceof \org\codehaus\stomp\ReceivedMessage);
  }
}
