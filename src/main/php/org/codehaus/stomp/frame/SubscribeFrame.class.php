<?php namespace org\codehaus\stomp\frame;

use \org\codehaus\stomp\Header;

/**
 * Subscribe frame
 *
 */
class SubscribeFrame extends Frame {

  /**
   * Constructor
   *
   * @see     xp://org.codehaus.stomp.AckMode
   * @param   string queue
   * @param   string ack default 'auto'
   * @param   string selector default NULL
   */
  public function __construct($queue, $ack= \org\codehaus\stomp\AckMode::AUTO, $selector= NULL) {
    $this->setDestination($queue);
    $this->setAck($ack);
    if (NULL !== $selector) $this->setSelector($selector);
  }

  /**
   * Frame command
   *
   */
  public function command() {
    return 'SUBSCRIBE';
  }

  /**
   * Set destination
   *
   * @param   string destination
   */
  public function setDestination($destination) {
    $this->addHeader(Header::DESTINATION, $destination);
  }

  /**
   * Get destination
   *
   */
  public function getDestination() {
    return $this->getHeader(Header::DESTINATION);
  }

  /**
   * Set selector
   *
   * @param   string selector
   */
  public function setSelector($selector) {
    $this->addHeader(Header::SELECTOR, $selector);
  }

  /**
   * Get selector
   *
   */
  public function getSelector() {
    return $this->getHeader(Header::SELECTOR);
  }

  /**
   * Set ack
   *
   * @see     xp://org.codehaus.stomp.AckMode
   * @param   string ack
   */
  public function setAck($ack) {
    $this->addHeader(Header::ACK, $ack);
  }

  /**
   * Get ack
   *
   * @return  string
   */
  public function getAck() {
    return $this->getHeader(Header::ACK);
  }

  /**
   * Set id
   *
   * @param   string id
   */
  public function setId($id) {
    $this->addHeader(Header::ID, $id);
  }

  /**
   * Get id
   *
   * @return  string
   */
  public function getId() {
    return $this->getHeader(Header::ID);
  }
}
