<?php namespace examples;

use org\codehaus\stomp\Connection;
use org\codehaus\stomp\Subscription;
use org\codehaus\stomp\AckMode;
use util\log\Logger;
use util\log\ColoredConsoleAppender;

class MultiConsume extends \util\cmd\Command {

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
    $sub1= $conn->subscribeTo(new Subscription('/queue/producer', function($message) use($self) {
      $self->out->writeLine('Acking message ', $message->getMessageId());
      $message->ack();
    }));
    $sub2= $conn->subscribeTo(new Subscription('/queue/foobar', function($message) use($self) {
      $self->out->writeLine('Consumed message ', $message->getMessageId());
    }, AckMode::AUTO));

    while ($conn->consume()) {}
  }
}
