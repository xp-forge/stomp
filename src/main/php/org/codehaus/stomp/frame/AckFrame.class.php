<?php namespace org\codehaus\stomp\frame;

use \org\codehaus\stomp\Header;

/**
 * Ack frame
 *
 */
class AckFrame extends Frame {

  /**
   * Constructor
   *
   * @param   string messageId
   * @param   string txname default NULL
   */
  public function __construct($messageId, $txname= NULL) {
    $this->setMessageId($messageId);
    if (NULL !== $txname) $this->setTransaction($txname);
  }

  /**
   * Frame command
   *
   */
  public function command() {
    return 'ACK';
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

  /**
   * Set message id
   *
   * @param   string messageId
   */
  public function setMessageId($messageId) {
    $this->addHeader(Header::MESSAGEID, $messageId);
  }

  /**
   * Get message id
   *
   */
  public function getMessageId() {
    return $this->getHeader(Header::MESSAGEID);
  }
}
