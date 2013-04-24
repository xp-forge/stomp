<?php namespace org\codehaus\stomp;

class Destination extends \lang\Object {
  protected $name   = NULL;
  protected $conn   = NULL;

  public function __construct($name, StompConnection $conn) {
    $this->name= $name;
    $this->conn= $conn;
  }

  public function getName() {
    return $this->name;
  }

  public function getConnection() {
    return $this->conn;
  }
}
