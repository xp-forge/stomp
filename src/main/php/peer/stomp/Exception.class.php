<?php namespace org\codehaus\stomp;

use org\codehaus\stomp\frame\Frame;

/**
 * Exception base class
 * 
 */
class Exception extends \io\IOException {
  private $frame = NULL;

  /**
   * Set frame
   *
   * @param  org.codehaus.stomp.frame.Frame $frame
   */
  public function setFrame(Frame $frame) {
    $this->frame= $frame;
  }

  /**
   * Set frame
   * 
   * @param  org.codehaus.stomp.frame.Frame $frame
   * @return org.codehaus.stomp.Exception
   */
  public function withFrame(Frame $frame) {
    $this->setFrame($frame);
    return $this;
  }
}
