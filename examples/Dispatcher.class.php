<?php namespace examples;

use org\codehaus\stomp\Connection;
use org\codehaus\stomp\Subscription;
use util\log\Logger;
use util\log\ColoredConsoleAppender;

class Dispatcher extends \util\cmd\Command {

  #[@arg]
  public function setDebug($d= FALSE) {
    if (NULL === $d) {
      Logger::getInstance()->getCategory()->withAppender(new ColoredConsoleAppender());
    }
  }

  public function run() {
    $conn= new Connection(new \peer\URL('stomp://localhost:61613/?log=default'));
    $conn->connect();

    $self= $this;
    $sub= $conn->subscribeTo(new Subscription('/queue/producer', function($message) use($self, $conn) {
      $self->out->writeLine('Consuming: ', \xp::stringOf($message));
      $cpy= $message->toSendable();
      $conn->getDestination('/queue/foobar')->send($cpy);

      $message->ack();
    }));

    while ($conn->consume(1)) {}
  }
}
