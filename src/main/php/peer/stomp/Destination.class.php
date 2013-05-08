<?php namespace org\codehaus\stomp;

/**
 * STOMP Destination
 * 
 */
class Destination extends \lang\Object {
  protected $name   = NULL;
  protected $conn   = NULL;

  /**
   * Constructor
   * 
   * @param string $name
   * @param org.codehaus.stomp.Connection $conn
   */
  public function __construct($name, Connection $conn) {
    $this->name= $name;
    $this->conn= $conn;
  }

  /**
   * Retrieve name
   * 
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Retrieve destination's connection
   * 
   * @return org.codehaus.stomp.Connection
   */
  public function getConnection() {
    return $this->conn;
  }

  /**
   * Send a message to destination
   * 
   * @param  org.codehaus.stomp.SendableMessage $message
   */
  public function send(SendableMessage $message) {
    $this->getConnection()->sendFrame($message->toFrame($this));
  }

  /**
   * Retrieve string representation
   * 
   * @return string
   */
  public function toString() {
    return $this->name.' -> '.\xp::stringOf($this->conn, '  ');
  }
}
