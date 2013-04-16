<?php
/* This class is part of the XP framework
 *
 * $Id$
 */
  uses('org.codehaus.stomp.frame.AckFrame');

  $package= 'org.codehaus.stomp.frame';

  /**
   * Nack frame
   *
   */
  class org·codehaus·stomp·frame·NackFrame extends org·codehaus·stomp·frame·AckFrame {

    /**
     * Frame command
     *
     */
    public function command() {
      return 'NACK';
    }
  }
?>
