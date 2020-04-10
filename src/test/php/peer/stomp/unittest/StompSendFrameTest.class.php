<?php namespace peer\stomp\unittest;

use peer\stomp\frame\SendFrame;

/**
 * Tests STOMP SendFrame class
 *
 * @see   xp://peer.stomp.unittest.StompFrameTest
 * @see   xp://peer.stomp.frame.SendFrame
 */
class StompSendFrameTest extends \unittest\TestCase {
  protected $fixture= null;

  /**
   * Sets up unittest and creates fixture
   */
  public function setUp() {
    $this->fixture= new SendFrame('/queue/test');
  }

  #[@test]
  public function setBodySetsContentLengthIfDefined() {
    $this->fixture->addHeader('content-length', 0);
    $this->fixture->setBody('Hello World');
    $this->assertEquals(11, $this->fixture->getHeader('content-length'));
  }

  #[@test]
  public function setBodyDoesNotSetContentLengthIfUndefined() {
    $this->fixture->setBody('Hello World');
    $this->assertFalse($this->fixture->hasHeader('content-length'));
  }
}