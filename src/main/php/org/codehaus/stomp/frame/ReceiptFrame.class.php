<?php namespace org\codehaus\stomp\frame;

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
?>
