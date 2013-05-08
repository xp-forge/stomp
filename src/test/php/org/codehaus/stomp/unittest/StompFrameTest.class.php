<?php namespace org\codehaus\stomp\unittest;
  
use peer\stomp\frame\Frame;
use peer\stomp\Header;

/**
 * Tests STOMP frame class
 *
 * @see   xp://peer.stomp.unittest.StompSendFrameTest
 * @see   xp://peer.stomp.frame.Frame
 */
class StompFrameTest extends \unittest\TestCase {
  protected $fixture= NULL;

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

  /**
   * Tests getHeaders()
   *
   */
  #[@test]
  public function getHeadersInitiallyEmpty() {
    $this->assertEquals(array(), $this->fixture->getHeaders());
  }

  /**
   * Tests addHeader() and hasHeader()
   *
   */
  #[@test]
  public function hasHeader() {
    $this->fixture->addHeader('content-length', 200);
    $this->assertTrue($this->fixture->hasHeader('content-length'));
  }

  /**
   * Tests addHeader() and getHeader()
   *
   */
  #[@test]
  public function getHeader() {
    $this->fixture->addHeader('content-length', 200);
    $this->assertEquals(200, $this->fixture->getHeader('content-length'));
  }

  /**
   * Tests getHeader()
   *
   */
  #[@test, @expect('lang.IllegalArgumentException')]
  public function getNonExistantHeader() {
    $this->fixture->getHeader('non-existant');
  }

  /**
   * Tests hasHeader()
   *
   */
  #[@test]
  public function hasNonExistantHeader() {
    $this->assertFalse($this->fixture->hasHeader('non-existant'));
  }

  /**
   * Tests addHeader() and getHeaders()
   *
   */
  #[@test]
  public function getHeaders() {
    $this->fixture->addHeader('content-length', 200);
    $this->assertEquals(array('content-length' => 200), $this->fixture->getHeaders());
  }

  /**
   * Tests requiresImmediateResponse()
   *
   */
  #[@test]
  public function receiptHeader() {
    $this->fixture->addHeader('receipt', 'message-12345');
    $this->assertTrue($this->fixture->requiresImmediateResponse());
  }

  /**
   * Test
   *
   */
  #[@test]
  public function set_want_receipt() {
    $this->fixture->setWantReceipt(TRUE);
    $this->assertTrue($this->fixture->hasHeader(Header::RECEIPT));
  }

  /**
   * Test
   *
   */
  #[@test]
  public function set_no_want_receipt() {
    $this->fixture->addHeader(Header::RECEIPT, "foo");
    $this->fixture->setWantReceipt(FALSE);
    $this->assertFalse($this->fixture->hasHeader(Header::RECEIPT));
  }

  /**
   * Test
   *
   */
  #[@test]
  public function clearHeader() {
    $this->fixture->addHeader("some-header", "some-value");
    $this->fixture->clearHeader("some-header");
    $this->assertFalse($this->fixture->hasHeader("some-header"));
  }
}
?>
