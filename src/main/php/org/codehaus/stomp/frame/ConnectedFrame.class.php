<?php namespace org\codehaus\stomp\frame;

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
      if (!$this->hasHeader('version')) return NULL;
      return $this->getHeader('version');
    }
  }
?>
