<?php namespace examples;

use org\codehaus\stomp\Connection;
use org\codehaus\stomp\SendableMessage;

class Producer extends \util\cmd\Command {
  protected $amount= 0;

  #[@arg(position= 0)]
  public function setAmount($i= 1) {
    $this->amount= $i;
  }

  public function run() {
    $conn= new Connection(new \peer\URL('stomp://localhost:61613/'));
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
