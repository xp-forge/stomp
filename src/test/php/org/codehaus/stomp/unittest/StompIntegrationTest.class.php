<?php namespace org\codehaus\stomp\unittest;

use peer\stomp\Connection;
use peer\stomp\SendableMessage;
use peer\stomp\frame\MessageFrame;
use peer\stomp\frame\ReceiptFrame;
use peer\stomp\frame\SendFrame;
use util\log\Logger;
use peer\URL;

class StompIntegrationTest extends \unittest\TestCase {
  const QUEUE = '/queue/unittest';

  protected static $serverProcess = null;
  protected static $bindAddress   = null;
  protected $fixture  = null;

  /**
   * Sets up test case
   *
   */
  #[@beforeClass]
  public static function startStompServer() {

    // Arguments to server process
    $args= array(
      'debugServerProtocolToFile' => null,
    );

    // Start server process
    self::$serverProcess= \lang\Runtime::getInstance()->newInstance(
      null, 
      'class', 
      'org.codehaus.stomp.unittest.TestingServer',
      array_values($args)
    );
    self::$serverProcess->in->close();

    // Check if startup succeeded
    $status= self::$serverProcess->out->readLine();
    if (1 != sscanf($status, '+ Service %[0-9.:]', self::$bindAddress)) {
      try {
        self::shutdownStompServer();
      } catch (\lang\IllegalStateException $e) {
        $status.= $e->getMessage();
      }
      throw new \unittest\PrerequisitesNotMetError('Cannot start STOMP server: '.$status, null);
    }
  }

  /**
   * Shut down FTP server
   *
   */
  #[@afterClass]
  public static function shutdownStompServer() {

    // Tell the STOMP server to shut down
    try {
      $c= new Connection(new URL('stomp://test:test@'.self::$bindAddress));
      $c->connect();
      $c->sendFrame(newinstance('peer.stomp.frame.Frame', array(), '{
        public function command() { return "SHUTDOWN"; }
        public function requiresImmediateResponse() { return FALSE; }
      }'));
      $c->disconnect();
    } catch (\lang\Throwable $ignored) {
      // Fall through, below should terminate the process anyway
    }

    $status= self::$serverProcess->out->readLine();
    if (!strlen($status) || '+' != $status{0}) {
      while ($l= self::$serverProcess->out->readLine()) {
        $status.= $l;
      }
      while ($l= self::$serverProcess->err->readLine()) {
        $status.= $l;
      }
      self::$serverProcess->close();
      throw new \lang\IllegalStateException($status);
    }
    self::$serverProcess->close();
  }

  #[@beforeClass]
  public static function logger() {
   // \util\log\Logger::getInstance()->getCategory()->addAppender(new \util\log\ColoredConsoleAppender());
  }

  public function setUp() {
    $this->fixture= new Connection(new URL('stomp://test:test@'.self::$bindAddress));
    $this->fixture->setTrace(\util\log\Logger::getInstance()->getCategory());
    $this->fixture->connect();
  }

  public function tearDown() {
    $this->fixture->disconnect();
  }

  #[@test, @expect('peer.AuthenticationException')]
  public function invalidCredentials() {
    $conn= new Connection(new URL('stomp://unknownuser:invalidpass@'.self::$bindAddress));
    $conn->connect();
  }

  #[@test]
  public function sendMessage() {
    $this->fixture->getDestination(self::QUEUE)->send(new SendableMessage('This is a text message'));
  }

  #[@test, @ignore('API changed')]
  public function subscribeAndReceive() {
    $this->fixture->subscribe(self::QUEUE, 'client');

    $message= $this->fixture->receive();
    $this->assertTrue($message instanceof MessageFrame);
  }

  #[@test, @ignore('API changed')]
  public function receiveReceipt() {
    $frame= new SendFrame(self::QUEUE, 'body');
    $frame->addHeader('receipt', 'some-message-receipt');

    $response= $this->fixture->sendFrame($frame);
    $this->assertTrue($response instanceof ReceiptFrame);
    $this->assertEquals($frame->getHeader('receipt'), $response->getHeader('receipt-id'));
  }

  #[@test, @ignore('API changed')]
  public function emptyQueue() {
    $this->fixture->subscribe(self::QUEUE, 'client');

    while ($message= $this->fixture->receive()) {
      $this->fixture->ack($message->getHeader('message-id'));
    }
  }
}
