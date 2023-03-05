<?php namespace peer\stomp\unittest;

use lang\IllegalArgumentException;
use peer\stomp\Header;
use peer\stomp\frame\Frame;
use test\{Assert, Before, Expect, Test};

/**
 * Tests STOMP frame class
 *
 * @see   peer.stomp.unittest.StompSendFrameTest
 * @see   peer.stomp.frame.Frame
 */
class StompFrameTest {
  private $fixture= null;

  #[Before]
  public function setUp() {
    $this->fixture= new class() extends Frame {
      public function command() { return 'test'; }
    };
  }

  #[Test]
  public function getHeadersInitiallyEmpty() {
    Assert::equals([], $this->fixture->getHeaders());
  }

  #[Test]
  public function hasHeader() {
    $this->fixture->addHeader('content-length', 200);
    Assert::true($this->fixture->hasHeader('content-length'));
  }

  #[Test]
  public function getHeader() {
    $this->fixture->addHeader('content-length', 200);
    Assert::equals(200, $this->fixture->getHeader('content-length'));
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function getNonExistantHeader() {
    $this->fixture->getHeader('non-existant');
  }

  #[Test]
  public function hasNonExistantHeader() {
    Assert::false($this->fixture->hasHeader('non-existant'));
  }

  #[Test]
  public function getHeaders() {
    $this->fixture->addHeader('content-length', 200);
    Assert::equals(['content-length' => 200], $this->fixture->getHeaders());
  }

  #[Test]
  public function receiptHeader() {
    $this->fixture->addHeader('receipt', 'message-12345');
    Assert::true($this->fixture->requiresImmediateResponse());
  }

  #[Test]
  public function set_want_receipt() {
    $this->fixture->setWantReceipt(true);
    Assert::true($this->fixture->hasHeader(Header::RECEIPT));
  }

  #[Test]
  public function set_no_want_receipt() {
    $this->fixture->addHeader(Header::RECEIPT, "foo");
    $this->fixture->setWantReceipt(false);
    Assert::false($this->fixture->hasHeader(Header::RECEIPT));
  }

  #[Test]
  public function clearHeader() {
    $this->fixture->addHeader("some-header", "some-value");
    $this->fixture->clearHeader("some-header");
    Assert::false($this->fixture->hasHeader("some-header"));
  }
}