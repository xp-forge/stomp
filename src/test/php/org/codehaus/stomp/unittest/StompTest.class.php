<?php namespace org\codehaus\stomp\unittest;

  use org\codehaus\stomp\StompConnection;

  /**
   * Tests STOMP protocol
   *
   * @see   http://stomp.github.com/stomp-specification-1.1.html#STOMP_Frames
   * @see   xp://org.codehaus.stomp.StompConnection
   */
  class StompTest extends BaseTest {

    /**
     * Tests connect message
     *
     */
    #[@test]
    public function connect() {
      $this->fixture->setResponseBytes("CONNECTED\n".
        "session-id:0xdeadbeef\n".
        "\n\0"
      );
      $this->fixture->connect('user', 'pass');

      $this->assertEquals("CONNECT\n".
        "login:user\n".
        "passcode:pass\n".
        "\n\0",
        $this->fixture->readSentBytes()
      );
    }

    /**
     * Tests connect message when login fails
     *
     */
    #[@test, @expect('peer.AuthenticationException')]
    public function loginFailed() {
      $this->fixture->setResponseBytes("ERROR\n".
        "message: Invalid credentials\n".
        "\n\0"
      );
      $this->fixture->connect('user', 'pass');
    }

    /**
     * Tests connect message
     *
     */
    #[@test]
    public function connect_and_negotiate_version() {
      $this->fixture->setResponseBytes("CONNECTED\n".
        "session-id:0xdeadbeef\n".
        "version:1.1\n".
        "\n\0"
      );
      $this->fixture->connect('user', 'pass', 'localhost', array('1.0', '1.1'));

      $this->assertEquals("CONNECT\n".
        "login:user\n".
        "passcode:pass\n".
        "accept-version:1.0,1.1\n".
        "host:localhost\n".
        "\n\0",
        $this->fixture->readSentBytes()
      );
    }

    /**
     * Tests connect message
     *
     */
    #[@test, @expect('peer.AuthenticationException')]
    public function connect_and_negotiate_version_but_fails() {
      $this->fixture->setResponseBytes("ERROR\n".
        "version:1.1\n".
        "content-type:text/plain\n".
        "\n".
        "Supported protocol versions are: 1.2".
        "\n\0"
      );
      $this->fixture->connect('user', 'pass', 'localhost', array('1.0', '1.1'));

      $this->assertEquals("CONNECT\n".
        "login:user\n".
        "passcode:pass\n".
        "accept-version:1.0,1.1\n".
        "host:localhost\n".
        "\n\0",
        $this->fixture->readSentBytes()
      );
    }

    /**
     * Test
     *
     */
    #[@test, @expect(class= 'lang.IllegalArgumentException', withMessage= '/Versions required when specifying hostname/')]
    public function connect_with_host_requires_versions() {
      $this->fixture->connect('user', 'pass', 'host');
    }

    /**
     * Tests send message
     *
     */
    #[@test]
    public function sendFrame() {
      $this->fixture->setResponseBytes("RECEIPT\n".
        "receipt-id:message-id\n".
        "\n\0"
      );

      $this->fixture->sendFrame(new \org\codehaus\stomp\frame\SendFrame('/queue/a', 'my-data'));
      $this->assertEquals("SEND\n".
        "destination:/queue/a\n".
        "\nmy-data\0",
        $this->fixture->readSentBytes()
      );
      $response= $this->fixture->receive();

      $this->assertInstanceOf('org.codehaus.stomp.frame.ReceiptFrame', $response);
    }

    /**
     * Tests error message
     *
     */
    #[@test]
    public function receiveError() {
      $this->fixture->setResponseBytes("ERROR\n".
        "message:Unknown command\n".
        "\n".
        "Line1\nLine2\0");

      $response= $this->fixture->recvFrame();
      $this->assertEquals('Unknown command', $response->getHeader('message'));
      $this->assertEquals("Line1\nLine2", $response->getBody());
    }

    /**
     * Tests error message w/ content-length
     *
     */
    #[@test]
    public function receiveErrorWithContentLengthGiven() {
      $this->fixture->setResponseBytes("ERROR\n".
        "message:Unknown command\n".
        "code:message:unknown\n".
        "content-length:11\n".
        "\n".
        "Line1\nLine2\0\n");

      $response= $this->fixture->recvFrame();
      $this->assertEquals('Unknown command', $response->getHeader('message'));
      $this->assertEquals('message:unknown', $response->getHeader('code'));
      $this->assertEquals("Line1\nLine2", $response->getBody());
    }

    /**
     * Tests message with invalid content-length
     *
     */
    #[@test, @expect('peer.ProtocolException')]
    public function catchInvalidContentLength() {
      $this->fixture->setResponseBytes("ERROR\n".
        "message:Unknown command\n".
        "content-length:10\n".
        "\n".
        "Content longer that 10 bytes.\0"
      );
      $response= $this->fixture->recvFrame();
    }

    /**
     * Helper
     *
     * @param   org.codehaus.stomp.frame.Frame fram
     */
    protected function sendWithReceiptFrame(\org\codehaus\stomp\frame\Frame $frame) {
      $this->fixture->setResponseBytes("RECEIPT\n".
        "receipt-id:message-id\n".
        "\n\0"
      );

      return $this->fixture->sendFrame($frame);
    }

    /**
     * Tests subscription
     *
     */
    #[@test]
    public function subscribe() {
      $this->sendWithReceiptFrame(new \org\codehaus\stomp\frame\SubscribeFrame('/queue/a'));
      $this->assertEquals("SUBSCRIBE\n".
        "destination:/queue/a\n".
        "ack:auto\n".
        "\n".
        "\0",
        $this->fixture->readSentBytes()
      );
    }

    /**
     * Tests subscription
     *
     */
    #[@test]
    public function unsubscribe() {
      $this->sendWithReceiptFrame(new \org\codehaus\stomp\frame\UnsubscribeFrame('/queue/a'));
      $this->assertEquals("UNSUBSCRIBE\n".
        "destination:/queue/a\n".
        "\n".
        "\0",
        $this->fixture->readSentBytes()
      );
    }

    /**
     * Tests beginning a transaction
     *
     */
    #[@test]
    public function beginTransaction() {
      $this->sendWithReceiptFrame(new \org\codehaus\stomp\frame\BeginFrame('my-transaction'));
      $this->assertEquals("BEGIN\n".
        "transaction:my-transaction\n\n\0"
        , $this->fixture->readSentBytes()
      );
    }

    /**
     * Tests aborting a transaction
     *
     */
    #[@test]
    public function abortTransaction() {
      $this->sendWithReceiptFrame(new \org\codehaus\stomp\frame\AbortFrame('my-transaction'));
      $this->assertEquals("ABORT\n".
        "transaction:my-transaction\n\n\0"
        , $this->fixture->readSentBytes()
      );
    }

    /**
     * Tests committing a transaction
     *
     */
    #[@test]
    public function commitTransaction() {
      $this->sendWithReceiptFrame(new \org\codehaus\stomp\frame\CommitFrame('my-transaction'));
      $this->assertEquals("COMMIT\n".
        "transaction:my-transaction\n\n\0"
        , $this->fixture->readSentBytes()
      );
    }

    /**
     * Tests ack message
     *
     */
    #[@test]
    public function ack() {
      $this->sendWithReceiptFrame(new \org\codehaus\stomp\frame\AckFrame('0xefefef'));
      $this->assertEquals("ACK\n".
        "message-id:0xefefef\n".
        "\n\0"
        , $this->fixture->readSentBytes()
      );
    }

    /**
     * Tests ack message
     *
     */
    #[@test]
    public function nack() {
      $this->sendWithReceiptFrame(new \org\codehaus\stomp\frame\NackFrame('0xefefef'));
      $this->assertEquals("NACK\n".
        "message-id:0xefefef\n".
        "\n\0"
        , $this->fixture->readSentBytes()
      );
    }

    /**
     * Tests ack message
     *
     */
    #[@test]
    public function ackWithinTransaction() {
      $this->sendWithReceiptFrame(new \org\codehaus\stomp\frame\AckFrame('0xefefef', "some-transaction"));
      $this->assertEquals("ACK\n".
        "message-id:0xefefef\n".
        "transaction:some-transaction\n".
        "\n\0"
        , $this->fixture->readSentBytes()
      );
    }

    /**
     * Tests ack message
     *
     */
    #[@test]
    public function nackWithinTransaction() {
      $this->sendWithReceiptFrame(new \org\codehaus\stomp\frame\NackFrame('0xefefef', "some-transaction"));
      $this->assertEquals("NACK\n".
        "message-id:0xefefef\n".
        "transaction:some-transaction\n".
        "\n\0"
        , $this->fixture->readSentBytes()
      );
    }

    /**
     * Tests disconnect
     *
     */
    #[@test]
    public function disconnect() {
      $this->fixture->disconnect();

      $this->assertEquals("DISCONNECT\n\n\0", $this->fixture->readSentBytes());
    }

    /**
     * Tests message without trailing "\n"
     *
     */
    #[@test]
    public function noTrailingEOL() {
      $this->fixture->setResponseBytes(
        "ERROR\n\nLine1\nLine2\0".
        "RECEIPT\nreceipt-id:77\n\n\0\n\n"
      );

      $response= $this->fixture->recvFrame();
      $receipt= $this->fixture->recvFrame();
      $this->assertEquals("Line1\nLine2", $response->getBody());
      $this->assertEquals('', $receipt->getBody());
    }

    /**
     * Tests message without trailing "\n"
     *
     */
    #[@test]
    public function withContentLengthNoTrailingEOL() {
      $this->fixture->setResponseBytes(
        "ERROR\ncontent-length:11\n\nLine1\nLine2\0".
        "RECEIPT\nreceipt-id:77\n\n\0\n\n"
      );

      $response= $this->fixture->recvFrame();
      $receipt= $this->fixture->recvFrame();
      $this->assertEquals("Line1\nLine2", $response->getBody());
      $this->assertEquals('', $receipt->getBody());
    }

    /**
     * Tests message with one trailing "\n"
     *
     */
    #[@test]
    public function oneTrailingEOL() {
      $this->fixture->setResponseBytes(
        "ERROR\n\nLine1\nLine2\0\n".
        "RECEIPT\nreceipt-id:77\n\n\0\n\n"
      );

      $response= $this->fixture->recvFrame();
      $receipt= $this->fixture->recvFrame();
      $this->assertEquals("Line1\nLine2", $response->getBody());
      $this->assertEquals('', $receipt->getBody());
    }

    /**
     * Tests message with one trailing "\n"
     *
     */
    #[@test]
    public function withContentLengthOneTrailingEOL() {
      $this->fixture->setResponseBytes(
        "ERROR\ncontent-length:11\n\nLine1\nLine2\0\n".
        "RECEIPT\nreceipt-id:77\n\n\0\n\n"
      );

      $response= $this->fixture->recvFrame();
      $receipt= $this->fixture->recvFrame();
      $this->assertEquals("Line1\nLine2", $response->getBody());
      $this->assertEquals('', $receipt->getBody());
    }

    /**
     * Tests message with two trailing "\n"s
     *
     */
    #[@test]
    public function twoTrailingEOLs() {
      $this->fixture->setResponseBytes(
        "ERROR\n\nLine1\nLine2\0\n\n".
        "RECEIPT\nreceipt-id:77\n\n\0\n\n"
      );

      $response= $this->fixture->recvFrame();
      $receipt= $this->fixture->recvFrame();
      $this->assertEquals("Line1\nLine2", $response->getBody());
      $this->assertEquals('', $receipt->getBody());
    }

    /**
     * Tests message with two trailing "\n"s
     *
     */
    #[@test]
    public function withContentLengthTwoTrailingEOLs() {
      $this->fixture->setResponseBytes(
        "ERROR\ncontent-length:11\n\nLine1\nLine2\0\n\n".
        "RECEIPT\nreceipt-id:77\n\n\0\n\n"
      );

      $response= $this->fixture->recvFrame();
      $receipt= $this->fixture->recvFrame();
      $this->assertEquals("Line1\nLine2", $response->getBody());
      $this->assertEquals('', $receipt->getBody());
    }
  }
?>
