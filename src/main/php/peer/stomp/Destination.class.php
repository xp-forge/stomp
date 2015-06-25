<?php namespace peer\stomp;

/**
 * STOMP Destination
 */
class Destination extends \lang\Object {
  protected $name   = null;
  protected $conn   = null;

  /**
   * Constructor
   * 
   * @param string $name
   * @param peer.stomp.Connection $conn
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
   * @return peer.stomp.Connection
   */
  public function getConnection() {
    return $this->conn;
  }

  /**
   * Send a message to destination
   * 
   * @param  peer.stomp.SendableMessage $message
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
