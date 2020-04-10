<?php namespace peer\stomp\unittest;
  
use io\streams\{MemoryInputStream, StringReader};
use peer\stomp\Header;
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
    $frame= $this->frame("message-id:1\ncontent-length:4\n\nbody\0");
    $this->assertEquals('body', $frame->getBody());
  }

  #[@test]
  public function frame_without_content_length() {
    $frame= $this->frame("message-id:1\n\nbody\0");
    $this->assertEquals('body', $frame->getBody());
  }

  #[@test]
  public function frame_with_zero_content_length() {
    $frame= $this->frame("message-id:1\ncontent-length:0\n\n\0");
    $this->assertEquals('', $frame->getBody());
  }

  #[@test]
  public function message_id_header() {
    $frame= $this->frame("message-id:1\ncontent-length:4\n\nbody\0");
    $this->assertEquals('1', $frame->getHeader(Header::MESSAGEID));
  }

  #[@test, @values([
  #  ['x-test:\c', ':'],
  #  ['x-test:\r', "\r"],
  #  ['x-test:\n', "\n"],
  #  ['x-test:\\\\', '\\'],
  #  ['x-test:a\cb', 'a:b'],
  #])]
  public function header_with_escape_sequence($header, $expected) {
    $frame= $this->frame("message-id:1\n".$header."\ncontent-length:4\n\nbody\0");
    $this->assertEquals($expected, $frame->getHeader('x-test'));
  }
}