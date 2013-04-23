<?php namespace org\codehaus\stomp\frame;

use \org\codehaus\stomp\Header;

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
    if (!$this->hasHeader(Header::VERSION)) return NULL;
    return $this->getHeader(Header::VERSION);
  }
}
