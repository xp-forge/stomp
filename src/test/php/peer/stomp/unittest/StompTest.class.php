<?php namespace peer\stomp\unittest;

use lang\IllegalArgumentException;
use peer\stomp\frame\{
  AbortFrame,
  AckFrame,
  BeginFrame,
  CommitFrame,
  Frame,
  MessageFrame,
  NackFrame,
  ReceiptFrame,
  SendFrame,
  SubscribeFrame,
  UnsubscribeFrame
};
use peer\stomp\{Connection, Destination, Exception};
use peer\{AuthenticationException, ProtocolException, URL};
use test\{Assert, Expect, Test};

/**
 * Tests STOMP protocol
 *
 * @see   http://stomp.github.com/stomp-specification-1.1.html#STOMP_Frames
 * @see   peer.stomp.Connection
 */
class StompTest {
  const PINGS= "\n\n\n\n";

  /** Helper */
  private function sendWithReceiptFrame(Connection $conn, Frame $frame) {
    $conn->setResponseBytes("RECEIPT\nreceipt-id:message-id\n\n\0");
    return $conn->sendFrame($frame);
  }

  #[Test]
  public function connect() {
    $conn= new TestingConnection('stomp://user:pass@localhost');
    $conn->setResponseBytes("CONNECTED\n".
      "session-id:0xdeadbeef\n".
      "\n\0"
    );
    $conn->connect();

    Assert::equals("CONNECT\n".
      "accept-version:1.0,1.1\n".
      "host:localhost\n".
      "login:user\n".
      "passcode:pass\n".
      "\n\0",
      $conn->readSentBytes()
    );
  }

  #[Test]
  public function login_without_credentials() {
    $conn= new TestingConnection();
    $conn->setResponseBytes("CONNECTED\n".
      "version:1.0\n".
      "session-id:0xdeadbeef\n".
      "\n\0"
    );
    $conn->connect();

    Assert::equals("CONNECT\n".
      "accept-version:1.0,1.1\n".
      "host:localhost\n".
      "\n\0",
      $conn->readSentBytes()
    );
  }

  #[Test, Expect(AuthenticationException::class)]
  public function loginFailed() {
    $conn= new TestingConnection();
    $conn->setResponseBytes("ERROR\n".
      "message: Invalid credentials\n".
      "\n\0"
    );
    $conn->connect();
  }

  #[Test]
  public function connect_and_negotiate_version() {
    $conn= new TestingConnection('stomp://user:pass@host?vhost=localhost&versions=1.0,1.1');
    $conn->setResponseBytes("CONNECTED\n".
      "session-id:0xdeadbeef\n".
      "version:1.1\n".
      "\n\0"
    );
    $conn->connect();

    Assert::equals("CONNECT\n".
      "accept-version:1.0,1.1\n".
      "host:localhost\n".
      "login:user\n".
      "passcode:pass\n".
      "\n\0",
      $conn->readSentBytes()
    );
  }

  #[Test, Expect(AuthenticationException::class)]
  public function connect_and_negotiate_version_but_fails() {
    $conn= new TestingConnection('stomp://user:pass@host?vhost=localhost&versions=1.0,1.1');
    $conn->setResponseBytes("ERROR\n".
      "version:1.1\n".
      "content-type:text/plain\n".
      "\n".
      "Supported protocol versions are: 1.2".
      "\n\0"
    );
    $conn->connect();

    Assert::equals("CONNECT\n".
      "accept-version:1.0,1.1\n".
      "host:localhost\n".
      "login:user\n".
      "passcode:pass\n".
      "\n\0",
      $conn->readSentBytes()
    );
  }

  #[Test, Expect(class: IllegalArgumentException::class, message: '/Invalid protocol version/')]
  public function connect_requires_valid_version() {
    (new TestingConnection('stomp://user:pass@host?versions='))->connect();
  }

  #[Test]
  public function sendFrame() {
    $conn= new TestingConnection();
    $conn->setResponseBytes("RECEIPT\n".
      "receipt-id:message-id\n".
      "\n\0"
    );

    $conn->sendFrame(new SendFrame('/queue/a', 'my-data'));
    Assert::equals("SEND\n".
      "destination:/queue/a\n".
      "\nmy-data\0",
      $conn->readSentBytes()
    );
    $response= $conn->recvFrame();

    Assert::instance(ReceiptFrame::class, $response);
  }

  #[Test]
  public function receiveError() {
    $conn= new TestingConnection();
    $conn->setResponseBytes("ERROR\n".
      "message:Unknown command\n".
      "\n".
      "Line1\nLine2\0");

    $response= $conn->recvFrame();
    Assert::equals('Unknown command', $response->getHeader('message'));
    Assert::equals("Line1\nLine2", $response->getBody());
  }

  #[Test]
  public function receiveErrorWithContentLengthGiven() {
    $conn= new TestingConnection();
    $conn->setResponseBytes("ERROR\n".
      "message:Unknown command\n".
      "code:message:unknown\n".
      "content-length:11\n".
      "\n".
      "Line1\nLine2\0\n");

    $response= $conn->recvFrame();
    Assert::equals('Unknown command', $response->getHeader('message'));
    Assert::equals('message:unknown', $response->getHeader('code'));
    Assert::equals("Line1\nLine2", $response->getBody());
  }

  #[Test, Expect(ProtocolException::class)]
  public function catchInvalidContentLength() {
    $conn= new TestingConnection();
    $conn->setResponseBytes("ERROR\n".
      "message:Unknown command\n".
      "content-length:10\n".
      "\n".
      "Content longer that 10 bytes.\0"
    );
    $response= $conn->recvFrame();
  }

  #[Test]
  public function recv_eats_any_empty_line_before_frame() {
    $conn= new TestingConnection();
    $conn->setResponseBytes(
      self::PINGS.
      "RECEIPT\n".
      "message_id:12345\n".
      "\n\0"
    );

    $recvd= $conn->recvFrame();
    Assert::instance(ReceiptFrame::class, $recvd);
  }

  #[Test]
  public function recv_eats_any_empty_line_between_frames() {
    $conn= new TestingConnection();
    $conn->setResponseBytes(
      "RECEIPT\n".
      "message_id:12345\n".
      "\n\0".
      self::PINGS.
      "MESSAGE\n".
      "message_id:12345\n".
      "\n\0"
    );

    $recvd= [];
    $recvd[]= $conn->recvFrame();
    $recvd[]= $conn->recvFrame();
    Assert::instance(ReceiptFrame::class, $recvd[0]);
    Assert::instance(MessageFrame::class, $recvd[1]);
  }

  #[Test, Expect(class: Exception::class, message: '/ACK received without/')]
  public function receive_throws_exception_on_error_frame() {
    $conn= new TestingConnection();
    $conn->setResponseBytes("ERROR\n".
      "message:ACK received without a subscription id for acknowledge!\n".
      "\n".
      "ACK received without a subscription id for acknowledge!".
      "\n\0"
    );

    $msg= $conn->receive();
  }

  #[Test]
  public function subscribe() {
    $conn= new TestingConnection();
    $this->sendWithReceiptFrame($conn, new SubscribeFrame('/queue/a'));
    Assert::equals("SUBSCRIBE\n".
      "destination:/queue/a\n".
      "ack:auto\n".
      "\n".
      "\0",
      $conn->readSentBytes()
    );
  }

  #[Test]
  public function unsubscribe() {
    $conn= new TestingConnection();
    $this->sendWithReceiptFrame($conn, new UnsubscribeFrame('/queue/a'));
    Assert::equals("UNSUBSCRIBE\n".
      "destination:/queue/a\n".
      "\n".
      "\0",
      $conn->readSentBytes()
    );
  }

  #[Test]
  public function beginTransaction() {
    $conn= new TestingConnection();
    $this->sendWithReceiptFrame($conn, new BeginFrame('my-transaction'));
    Assert::equals("BEGIN\n".
      "transaction:my-transaction\n\n\0"
      , $conn->readSentBytes()
    );
  }

  #[Test]
  public function abortTransaction() {
    $conn= new TestingConnection();
    $this->sendWithReceiptFrame($conn, new AbortFrame('my-transaction'));
    Assert::equals("ABORT\n".
      "transaction:my-transaction\n\n\0"
      , $conn->readSentBytes()
    );
  }

  #[Test]
  public function commitTransaction() {
    $conn= new TestingConnection();
    $this->sendWithReceiptFrame($conn, new CommitFrame('my-transaction'));
    Assert::equals("COMMIT\n".
      "transaction:my-transaction\n\n\0"
      , $conn->readSentBytes()
    );
  }

  #[Test]
  public function ack() {
    $conn= new TestingConnection();
    $this->sendWithReceiptFrame($conn, new AckFrame('0xefefef', '1x1x1x1x1x1'));
    Assert::equals("ACK\n".
      "message-id:0xefefef\n".
      "subscription:1x1x1x1x1x1\n".
      "\n\0"
      , $conn->readSentBytes()
    );
  }

  #[Test]
  public function nack() {
    $conn= new TestingConnection();
    $this->sendWithReceiptFrame($conn, new NackFrame('0xefefef', '0x0x0x0x0'));
    Assert::equals("NACK\n".
      "message-id:0xefefef\n".
      "subscription:0x0x0x0x0\n".
      "\n\0"
      , $conn->readSentBytes()
    );
  }

  #[Test]
  public function ackWithinTransaction() {
    $conn= new TestingConnection();
    $this->sendWithReceiptFrame($conn, new AckFrame('0xefefef', 'some-subscription', "some-transaction"));
    Assert::equals("ACK\n".
      "message-id:0xefefef\n".
      "subscription:some-subscription\n".
      "transaction:some-transaction\n".
      "\n\0"
      , $conn->readSentBytes()
    );
  }

  #[Test]
  public function nackWithinTransaction() {
    $conn= new TestingConnection();
    $this->sendWithReceiptFrame($conn, new NackFrame('0xefefef', 'some-subscription', "some-transaction"));
    Assert::equals("NACK\n".
      "message-id:0xefefef\n".
      "subscription:some-subscription\n".
      "transaction:some-transaction\n".
      "\n\0"
      , $conn->readSentBytes()
    );
  }

  #[Test]
  public function disconnect() {
    $conn= new TestingConnection();
    $conn->disconnect();

    Assert::equals("DISCONNECT\n\n\0", $conn->readSentBytes());
  }

  #[Test]
  public function noTrailingEOL() {
    $conn= new TestingConnection();
    $conn->setResponseBytes(
      "ERROR\n\nLine1\nLine2\0".
      "RECEIPT\nreceipt-id:77\n\n\0\n\n"
    );

    $response= $conn->recvFrame();
    $receipt= $conn->recvFrame();
    Assert::equals("Line1\nLine2", $response->getBody());
    Assert::equals('', $receipt->getBody());
  }

  #[Test]
  public function withContentLengthNoTrailingEOL() {
    $conn= new TestingConnection();
    $conn->setResponseBytes(
      "ERROR\ncontent-length:11\n\nLine1\nLine2\0".
      "RECEIPT\nreceipt-id:77\n\n\0\n\n"
    );

    $response= $conn->recvFrame();
    $receipt= $conn->recvFrame();
    Assert::equals("Line1\nLine2", $response->getBody());
    Assert::equals('', $receipt->getBody());
  }

  #[Test]
  public function oneTrailingEOL() {
    $conn= new TestingConnection();
    $conn->setResponseBytes(
      "ERROR\n\nLine1\nLine2\0\n".
      "RECEIPT\nreceipt-id:77\n\n\0\n\n"
    );

    $response= $conn->recvFrame();
    $receipt= $conn->recvFrame();
    Assert::equals("Line1\nLine2", $response->getBody());
    Assert::equals('', $receipt->getBody());
  }

  #[Test]
  public function withContentLengthOneTrailingEOL() {
    $conn= new TestingConnection();
    $conn->setResponseBytes(
      "ERROR\ncontent-length:11\n\nLine1\nLine2\0\n".
      "RECEIPT\nreceipt-id:77\n\n\0\n\n"
    );

    $response= $conn->recvFrame();
    $receipt= $conn->recvFrame();
    Assert::equals("Line1\nLine2", $response->getBody());
    Assert::equals('', $receipt->getBody());
  }

  #[Test]
  public function twoTrailingEOLs() {
    $conn= new TestingConnection();
    $conn->setResponseBytes(
      "ERROR\n\nLine1\nLine2\0\n\n".
      "RECEIPT\nreceipt-id:77\n\n\0\n\n"
    );

    $response= $conn->recvFrame();
    $receipt= $conn->recvFrame();
    Assert::equals("Line1\nLine2", $response->getBody());
    Assert::equals('', $receipt->getBody());
  }

  #[Test]
  public function withContentLengthTwoTrailingEOLs() {
    $conn= new TestingConnection();
    $conn->setResponseBytes(
      "ERROR\ncontent-length:11\n\nLine1\nLine2\0\n\n".
      "RECEIPT\nreceipt-id:77\n\n\0\n\n"
    );

    $response= $conn->recvFrame();
    $receipt= $conn->recvFrame();
    Assert::equals("Line1\nLine2", $response->getBody());
    Assert::equals('', $receipt->getBody());
  }

  #[Test]
  public function acquire_destination() {
    $conn= new TestingConnection();
    Assert::instance(Destination::class, $conn->getDestination('/queue/unittest'));
  }

  #[Test]
  public function destination_holds_name() {
    $conn= new TestingConnection();
    Assert::equals(
      '/queue/unittest',
      $conn->getDestination('/queue/unittest')->getName()
    );
  }

  #[Test]
  public function destination_holds_connection() {
    $conn= new TestingConnection();
    Assert::equals(
      $conn,
      $conn->getDestination('/queue/unittest')->getConnection()
    );
  }
}