<?php namespace peer\stomp\frame;

use peer\stomp\Header;

/**
 * Connected frame
 *
 */
class ConnectedFrame extends Frame {

  /**
   * Frame command
   *
   */
  public function command() {
    return 'CONNECTED';
  }

  /**
   * Retrieve protocol version
   *
   * @return  string
   */
  public function getProtocolVersion() {
    if (!$this->hasHeader(Header::VERSION)) return null;
    return $this->getHeader(Header::VERSION);
  }
}