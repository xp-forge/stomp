<?php namespace peer\stomp\frame;

/**
 * Receipt frame
 *
 */
class ReceiptFrame extends Frame {

  /**
   * Frame command
   *
   */
  public function command() {
    return 'RECEIPT';
  }
}
