<?php namespace peer\stomp\unittest;
  
use io\streams\MemoryInputStream;
use io\streams\StringReader;
use peer\stomp\frame\MessageFrame;
use unittest\TestCase;

class FrameFromWireTest extends TestCase {

  /**
   * Creates a fram from the wire
   *
   * @param  string $bytes
   * @return peer.stomp.frame.Frame
   */
  private function frame($bytes) {
    $frame= new MessageFrame();
    $frame->fromWire(new StringReader(new MemoryInputStream($bytes)));
    return $frame;
  }

  #[@test]
  public function frame_with_content_length() {
    $frame= $this->frame("message-id: 1\ncontent-length: 4\n\nbody\0");
    $this->assertEquals('body', $frame->getBody());
  }

  #[@test]
  public function frame_without_content_length() {
    $frame= $this->frame("message-id: 1\n\nbody\0");
    $this->assertEquals('body', $frame->getBody());
  }
}