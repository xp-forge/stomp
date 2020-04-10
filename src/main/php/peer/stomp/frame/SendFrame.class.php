<?php namespace peer\stomp\frame;

use peer\stomp\Header;

/**
 * Send frame
 *
 * @test  xp://peer.stomp.unittest.StompSendFrameTest
 */
class SendFrame extends Frame {

  /**
   * Constructor
   *
   * @param   string destination
   * @param   string data default null
   * @param   [:string] headers default array
   */
  public function __construct($destination, $data= null, $headers= []) {
    $this->headers= $headers;
    $this->setDestination($destination);
    $this->setBody($data);
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
   * Set transaction
   *
   * @param   string name
   */
  public function setTransaction($name) {
    $this->addHeader(Header::TRANSACTION, $name);
  }

  /**
   * Get transaction
   *
   * @return  string
   */
  public function getTransaction() {
    return $this->getHeader(Header::TRANSACTION);
  }

  /**
   * Set body
   *
   * @param   string data
   */
  public function setBody($data) {
    parent::setBody($data);
    if ($this->hasHeader(Header::CONTENTLENGTH)) {
      $this->addHeader(Header::CONTENTLENGTH, strlen($this->body));
    }
  }

  /**
   * Frame command
   *
   */
  public function command() {
    return 'SEND';
  }
}