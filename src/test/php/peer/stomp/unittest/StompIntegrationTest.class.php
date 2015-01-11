<?php namespace peer\stomp\unittest;

use peer\stomp\Connection;
use peer\stomp\frame\MessageFrame;
use peer\stomp\frame\ReceiptFrame;
use unittest\PrerequisitesNotMetError;

class StompIntegrationTest extends \unittest\TestCase {
  const QUEUE = '/queue/unittest';

  protected $fixture= null;
  protected $host= null;

  public function __construct($name, $host= null) {
    parent::__construct($name);
    $this->host= $host;
  }

  #[@beforeClass]
  public static function logger() {
    // \util\log\Logger::getInstance()->getCategory()->addAppender(new \util\log\ColoredConsoleAppender());
  }

  public function setUp() {
    if (null === $this->host) {
      throw new PrerequisitesNotMetError('Needs argument "host"');
    }

    $this->fixture= new Connection(new \peer\URL('stomp://'.$this->host));
    $this->fixture->setTrace(\util\log\Logger::getInstance()->getCategory());
    $this->fixture->connect('system', 'manager');
  }

  public function tearDown() {
    $this->fixture->disconnect();
  }

  #[@test, @ignore, @expect('peer.AuthenticationException')]
  public function invalidCredentials() {
    $conn= new Connection(new \peer\URL('stomp://'.$this->host));
    $conn->connect('unknownuser', 'invalidpass');
  }

  #[@test]
  public function sendMessage() {
    $this->fixture->send(self::QUEUE, 'This is a text message');
  }

  #[@test]
  public function subscribeAndReceive() {
    $this->fixture->subscribe(self::QUEUE, 'client');

    $message= $this->fixture->receive();
    $this->assertTrue($message instanceof MessageFrame);
  }

  #[@test]
  public function receiveReceipt() {
    $frame= new \peer\stomp\SendFrame(self::QUEUE, 'body');
    $frame->addHeader('receipt', 'some-message-receipt');

    $response= $this->fixture->sendFrame($frame);
    $this->assertTrue($response instanceof ReceiptFrame);
    $this->assertEquals($frame->getHeader('receipt'), $response->getHeader('receipt-id'));
  }

  #[@test]
  public function emptyQueue() {
    $this->fixture->subscribe(self::QUEUE, 'client');

    while ($message= $this->fixture->receive()) {
      $this->fixture->ack($message->getHeader('message-id'));
    }
  }
}
