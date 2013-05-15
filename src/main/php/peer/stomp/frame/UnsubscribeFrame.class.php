<?php namespace peer\stomp\frame;

use peer\stomp\Header;

/**
 * Unsubscribe frame
 *
 */
class UnsubscribeFrame extends Frame {

  /**
   * Constructor
   *
   * @param   string queue
   * @param   string id default NULL
   */
  public function __construct($queue, $id= NULL) {
    if (NULL === $queue && NULL === $id) throw new \lang\IllegalArgumentException(
      'Either destination or id must be given.'
    );

    if (NULL !== $queue) {
      $this->setDestination($queue);
    } else {
      $this->setId($id);
    }
  }

  /**
   * Frame command
   *
   */
  public function command() {
    return 'UNSUBSCRIBE';
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
