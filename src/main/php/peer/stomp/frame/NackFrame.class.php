<?php namespace org\codehaus\stomp\frame;

/**
 * Nack frame
 *
 */
class NackFrame extends AckFrame {

  /**
   * Frame command
   *
   */
  public function command() {
    return 'NACK';
  }
}
