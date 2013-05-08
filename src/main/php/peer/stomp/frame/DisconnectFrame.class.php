<?php namespace peer\stomp\frame;

/**
 * Disconnect frame
 *
 */
class DisconnectFrame extends Frame {

  /**
   * Frame command
   *
   */
  public function command() {
    return 'DISCONNECT';
  }
}