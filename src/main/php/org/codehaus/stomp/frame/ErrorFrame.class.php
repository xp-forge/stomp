<?php namespace org\codehaus\stomp\frame;

use org\codehaus\stomp\Header;

/**
 * Error frame
 *
 */
class ErrorFrame extends Frame {

  /**
   * Frame command
   *
   */
  public function command() {
    return 'ERROR';
  }

  /**
   * Retrieve error message
   * @return string
   */
  public function getMessage() {
    return $this->getHeader(Header::MESSAGE);
  }
}
