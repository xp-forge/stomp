<?php namespace org\codehaus\stomp\frame;

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
  }
?>
