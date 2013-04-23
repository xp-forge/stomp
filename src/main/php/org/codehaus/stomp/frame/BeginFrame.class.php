<?php namespace org\codehaus\stomp\frame;

use \org\codehaus\stomp\Header;

/**
 * Begin frame
 *
 */
class BeginFrame extends Frame {

  /**
   * Constructor
   *
   * @param   string txname
   */
  public function __construct($txname) {
    $this->setTransaction($txname);
  }

  /**
   * Frame command
   *
   */
  public function command() {
    return 'BEGIN';
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
    $this->getHeader(Header::TRANSACTION);
  }
}