<?php namespace org\codehaus\stomp\unittest;

/**
 * Tests STOMP SendFrame class
 *
 * @see   xp://peer.stomp.unittest.StompFrameTest
 * @see   xp://peer.stomp.frame.SendFrame
 */
class StompSendFrameTest extends \unittest\TestCase {
  protected $fixture= NULL;

  /**
   * Sets up unittest and creates fixture
   *
   */
  public function setUp() {
    $this->fixture= new \peer\stomp\frame\SendFrame('/queue/test');
  }

  /**
   * Tests setBody()
   *
   */
  #[@test]
  public function setBodySetsContentLengthIfDefined() {
    $this->fixture->addHeader('content-length', 0);
    $this->fixture->setBody('Hello World');
    $this->assertEquals(11, $this->fixture->getHeader('content-length'));
  }

  /**
   * Tests setBody()
   *
   */
  #[@test]
  public function setBodyDoesNotSetContentLengthIfUndefined() {
    $this->fixture->setBody('Hello World');
    $this->assertFalse($this->fixture->hasHeader('content-length'));
  }
}
