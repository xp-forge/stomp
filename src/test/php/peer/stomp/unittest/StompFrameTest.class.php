<?php namespace peer\stomp\unittest;
  
use peer\stomp\frame\Frame;
use peer\stomp\Header;

/**
 * Tests STOMP frame class
 *
 * @see   xp://peer.stomp.unittest.StompSendFrameTest
 * @see   xp://peer.stomp.frame.Frame
 */
class StompFrameTest extends \unittest\TestCase {
  protected $fixture= null;

  /**
   * Sets up unittest and creates fixture
   *
   */
  public function setUp() {
    $this->fixture= newinstance('peer.stomp.frame.Frame', array(), '{
      public function command() { 
        return "test"; 
      }
    }');
  }

  #[@test]
  public function getHeadersInitiallyEmpty() {
    $this->assertEquals(array(), $this->fixture->getHeaders());
  }

  #[@test]
  public function hasHeader() {
    $this->fixture->addHeader('content-length', 200);
    $this->assertTrue($this->fixture->hasHeader('content-length'));
  }

  #[@test]
  public function getHeader() {
    $this->fixture->addHeader('content-length', 200);
    $this->assertEquals(200, $this->fixture->getHeader('content-length'));
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function getNonExistantHeader() {
    $this->fixture->getHeader('non-existant');
  }

  #[@test]
  public function hasNonExistantHeader() {
    $this->assertFalse($this->fixture->hasHeader('non-existant'));
  }

  #[@test]
  public function getHeaders() {
    $this->fixture->addHeader('content-length', 200);
    $this->assertEquals(array('content-length' => 200), $this->fixture->getHeaders());
  }

  #[@test]
  public function receiptHeader() {
    $this->fixture->addHeader('receipt', 'message-12345');
    $this->assertTrue($this->fixture->requiresImmediateResponse());
  }

  #[@test]
  public function set_want_receipt() {
    $this->fixture->setWantReceipt(true);
    $this->assertTrue($this->fixture->hasHeader(Header::RECEIPT));
  }

  #[@test]
  public function set_no_want_receipt() {
    $this->fixture->addHeader(Header::RECEIPT, "foo");
    $this->fixture->setWantReceipt(false);
    $this->assertFalse($this->fixture->hasHeader(Header::RECEIPT));
  }

  #[@test]
  public function clearHeader() {
    $this->fixture->addHeader("some-header", "some-value");
    $this->fixture->clearHeader("some-header");
    $this->assertFalse($this->fixture->hasHeader("some-header"));
  }
}
