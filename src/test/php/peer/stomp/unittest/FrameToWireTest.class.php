<?php namespace peer\stomp\unittest;
  
use io\streams\{MemoryOutputStream, StringWriter};
use peer\stomp\Header;
use peer\stomp\frame\MessageFrame;
use unittest\{Test, TestCase, Values};

class FrameToWireTest extends TestCase {

  /**
   * Creates a fram from the wire
   *
   * @param  string $bytes
   * @return peer.stomp.frame.Frame
   */
  private function write($frame) {
    $out= new MemoryOutputStream();
    $frame->write(new StringWriter($out));
    return $out->bytes();
  }

  #[Test]
  public function frame_with_content_length() {
    $frame= new MessageFrame();
    $frame->setBody('body');
    $frame->addHeader(Header::CONTENTLENGTH, 4);
    $this->assertEquals("MESSAGE\ncontent-length:4\n\nbody\0", $this->write($frame));
  }

  #[Test]
  public function frame_without_content_length() {
    $frame= new MessageFrame();
    $frame->setBody('body');
    $this->assertEquals("MESSAGE\n\nbody\0", $this->write($frame));
  }

  #[Test]
  public function frame_without_body() {
    $frame= new MessageFrame();
    $this->assertEquals("MESSAGE\n\n\0", $this->write($frame));
  }

  #[Test, Values([[':', 'x-test:\c'], ["\r", 'x-test:\r'], ["\n", 'x-test:\n'], ['\\', 'x-test:\\\\'], ['a:b', 'x-test:a\cb'],])]
  public function header_with_escape_sequence($value, $expected) {
    $frame= new MessageFrame();
    $frame->addHeader('x-test', $value);
    $this->assertEquals("MESSAGE\n".$expected."\n\n\0", $this->write($frame));
  }
}