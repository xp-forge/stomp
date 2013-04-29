<?php namespace examples;

use \org\codehaus\stomp\Connection;
use \org\codehaus\stomp\Subscription;
use \org\codehaus\stomp\ReceivedMessage;
use \util\log\Logger;
use \util\log\LogCategory;
use \util\log\ColoredConsoleAppender;

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

    $conn->consume(NULL);
  }
}
