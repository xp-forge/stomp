<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
  uses('org.codehaus.stomp.frame.Frame');

  $package= 'org.codehaus.stomp.frame';

  /**
   * Connected frame
   *
   */
  class org·codehaus·stomp·frame·ConnectedFrame extends org·codehaus·stomp·frame·Frame {

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
