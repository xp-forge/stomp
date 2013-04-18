<?php namespace org\codehaus\stomp\frame;

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
?>
