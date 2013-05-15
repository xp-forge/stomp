<?php namespace examples;

use peer\stomp\Connection;
use peer\stomp\Subscription;
use peer\stomp\ReceivedMessage;
use util\log\Logger;
use util\log\ColoredConsoleAppender;

class Consumer extends \util\cmd\Command {

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
    $sub= $conn->subscribeTo(new Subscription('/queue/producer', function(ReceivedMessage $msg) use($self) {
      $self->out->writeLine('Consuming: ', \xp::stringOf($msg));

      if ($msg) {
        $msg->ack();
      }
    }));

    while ($conn->consume(1)) {}

    $conn->disconnect();
  }
}
