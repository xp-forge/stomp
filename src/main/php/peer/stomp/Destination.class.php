<?php namespace peer\stomp;

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
   * @return string
   */
  public function send(SendableMessage $message) {
    $frame= $message->toFrame($this);
    if ($frame->requiresImmediateResponse()) {
      $receipt= $this->getConnection()->sendFrame($frame);
      return cast($receipt, 'peer.stomp.frame.ReceiptFrame')->getHeader(Header::RECEIPTID);
    } else {
      $this->getConnection()->sendFrame($frame);    // Fire and forget
      return null;
    }
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
