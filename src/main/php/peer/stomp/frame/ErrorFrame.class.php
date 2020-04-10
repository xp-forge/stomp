<?php namespace peer\stomp\frame;

use peer\stomp\Header;

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