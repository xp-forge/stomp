<?php namespace peer\stomp\frame;

/**
 * Message frame
 *
 */
class MessageFrame extends Frame {

  /**
   * Frame command
   *
   */
  public function command() {
    return 'MESSAGE';
  }
}
