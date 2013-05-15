<?php namespace examples;

use peer\stomp\Connection;
use peer\stomp\SendableMessage;
use util\log\Logger;
use util\log\ColoredConsoleAppender;

class Producer extends \util\cmd\Command {
  protected $amount= 0;

  #[@arg(position= 0)]
  public function setAmount($i= 1) {
    $this->amount= $i;
  }

  #[@arg]
  public function setDebug($d= FALSE) {
    if (NULL === $d) {
      Logger::getInstance()->getCategory()->withAppender(new ColoredConsoleAppender());
    }
  }

  public function run() {
    $conn= new Connection(new \peer\URL('stomp://localhost:61613/?log=default'));
    $conn->connect();

    $dest= $conn->getDestination('/queue/producer');
    for ($i= 1; $i <= $this->amount; $i++) {
      $msg= new SendableMessage(
        'Message '.$i.' of '.$this->amount.' in '.$this->hashCode(),
        'text/plain'
      );

      $dest->send($msg);
      $this->out->writeLine('Wrote message '.$i);
    }
  }
}
