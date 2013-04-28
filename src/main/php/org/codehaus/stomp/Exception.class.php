<?php namespace org\codehaus\stomp;
class Exception extends \io\IOException {
  private $frame = NULL;

  public function setFrame(frame\Frame $frame) {
    $this->frame= $frame;
  }

  public function withFrame(frame\Frame $frame) {
    $this->setFrame($frame);
    return $this;
  }
}
