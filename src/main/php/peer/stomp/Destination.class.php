<?php namespace peer\stomp;

use lang\Value;

/**
 * STOMP Destination
 */
class Destination implements Value {
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
   * Compare
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    if ($value instanceof self && $this->conn === $value->conn) {
      return strcmp($this->name, $value->name);
    } else {
      return 1;
    }
  }

  /** @return string */
  public function hashCode() {
    return '@'.$this->name;
  }

  /** @return string */
  public function toString() {
    return $this->name.' -> '.\xp::stringOf($this->conn, '  ');
  }
}
