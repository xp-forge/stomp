<?php namespace peer\stomp\unittest;

use peer\stomp\frame\SendFrame;
use test\{Assert, Test};

/**
 * Tests STOMP SendFrame class
 *
 * @see   peer.stomp.unittest.StompFrameTest
 * @see   peer.stomp.frame.SendFrame
 */
class StompSendFrameTest {

  #[Test]
  public function setBodySetsContentLengthIfDefined() {
    $frame= new SendFrame('/queue/test');
    $frame->addHeader('content-length', 0);
    $frame->setBody('Hello World');

    Assert::equals(11, $frame->getHeader('content-length'));
  }

  #[Test]
  public function setBodyDoesNotSetContentLengthIfUndefined() {
    $frame= new SendFrame('/queue/test');
    $frame->setBody('Hello World');

    Assert::false($frame->hasHeader('content-length'));
  }
}