<?php namespace peer\stomp\unittest;
  
use lang\IllegalArgumentException;
use peer\stomp\Header;
use peer\stomp\frame\Frame;
use unittest\{Expect, Test, TestCase};

/**
 * Tests STOMP frame class
 *
 * @see   xp://peer.stomp.unittest.StompSendFrameTest
 * @see   xp://peer.stomp.frame.Frame
 */
class StompFrameTest extends TestCase {
  private $fixture= null;

  /** @return void */
  public function setUp() {
    $this->fixture= new class() extends Frame {
      public function command() { return 'test'; }
    };
  }

  #[Test]
  public function getHeadersInitiallyEmpty() {
    $this->assertEquals([], $this->fixture->getHeaders());
  }

  #[Test]
  public function hasHeader() {
    $this->fixture->addHeader('content-length', 200);
    $this->assertTrue($this->fixture->hasHeader('content-length'));
  }

  #[Test]
  public function getHeader() {
    $this->fixture->addHeader('content-length', 200);
    $this->assertEquals(200, $this->fixture->getHeader('content-length'));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function getNonExistantHeader() {
    $this->fixture->getHeader('non-existant');
  }

  #[Test]
  public function hasNonExistantHeader() {
    $this->assertFalse($this->fixture->hasHeader('non-existant'));
  }

  #[Test]
  public function getHeaders() {
    $this->fixture->addHeader('content-length', 200);
    $this->assertEquals(['content-length' => 200], $this->fixture->getHeaders());
  }

  #[Test]
  public function receiptHeader() {
    $this->fixture->addHeader('receipt', 'message-12345');
    $this->assertTrue($this->fixture->requiresImmediateResponse());
  }

  #[Test]
  public function set_want_receipt() {
    $this->fixture->setWantReceipt(true);
    $this->assertTrue($this->fixture->hasHeader(Header::RECEIPT));
  }

  #[Test]
  public function set_no_want_receipt() {
    $this->fixture->addHeader(Header::RECEIPT, "foo");
    $this->fixture->setWantReceipt(false);
    $this->assertFalse($this->fixture->hasHeader(Header::RECEIPT));
  }

  #[Test]
  public function clearHeader() {
    $this->fixture->addHeader("some-header", "some-value");
    $this->fixture->clearHeader("some-header");
    $this->assertFalse($this->fixture->hasHeader("some-header"));
  }
}