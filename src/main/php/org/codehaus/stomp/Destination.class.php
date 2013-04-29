<?php namespace org\codehaus\stomp;

class Destination extends \lang\Object {
  protected $name   = NULL;
  protected $conn   = NULL;

  public function __construct($name, Connection $conn) {
    $this->name= $name;
    $this->conn= $conn;
  }

  public function getName() {
    return $this->name;
  }

  public function getConnection() {
    return $this->conn;
  }

  public function subscribe($ackMode= AckMode::INDIVIDUAL, $selector= NULL) {
    return new Subscription($this->getName(), $ackMode, $selector);
  }

  public function send(SendableMessage $message) {
    $this->getConnection()->sendFrame($message->toFrame($this));
  }

  public function toString() {
    return $this->name.' -> '.\xp::stringOf($this->conn, '  ');
  }
}
