<?php namespace peer\stomp\unittest;

use io\streams\{MemoryInputStream, StringReader};
use peer\stomp\Header;
use peer\stomp\frame\MessageFrame;
use test\Assert;
use test\{Test, TestCase, Values};

class FrameFromWireTest {

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

  #[Test]
  public function frame_with_content_length() {
    $frame= $this->frame("message-id:1\ncontent-length:4\n\nbody\0");
    Assert::equals('body', $frame->getBody());
  }

  #[Test]
  public function frame_without_content_length() {
    $frame= $this->frame("message-id:1\n\nbody\0");
    Assert::equals('body', $frame->getBody());
  }

  #[Test]
  public function frame_with_zero_content_length() {
    $frame= $this->frame("message-id:1\ncontent-length:0\n\n\0");
    Assert::equals('', $frame->getBody());
  }

  #[Test]
  public function message_id_header() {
    $frame= $this->frame("message-id:1\ncontent-length:4\n\nbody\0");
    Assert::equals('1', $frame->getHeader(Header::MESSAGEID));
  }

  #[Test, Values([['x-test:\c', ':'], ['x-test:\r', "\r"], ['x-test:\n', "\n"], ['x-test:\\\\', '\\'], ['x-test:a\cb', 'a:b'],])]
  public function header_with_escape_sequence($header, $expected) {
    $frame= $this->frame("message-id:1\n".$header."\ncontent-length:4\n\nbody\0");
    Assert::equals($expected, $frame->getHeader('x-test'));
  }
}