<?php namespace peer\stomp;

use peer\stomp\frame\Frame;

/**
 * Exception base class
 */
class Exception extends \io\IOException {
  private $frame;

  /**
   * Set frame
   *
   * @param  peer.stomp.frame.Frame $frame
   */
  public function setFrame(Frame $frame) {
    $this->frame= $frame;
  }

  /**
   * Set frame
   * 
   * @param  peer.stomp.frame.Frame $frame
   * @return peer.stomp.Exception
   */
  public function withFrame(Frame $frame) {
    $this->setFrame($frame);
    return $this;
  }
}
