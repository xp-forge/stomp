<?php namespace org\codehaus\stomp\unittest;

use peer\stomp\Connection;
use peer\stomp\SendableMessage;
use peer\stomp\Subscription;
use peer\stomp\frame\MessageFrame;
use peer\stomp\frame\ReceiptFrame;
use peer\stomp\frame\SendFrame;
use util\log\Logger;
use peer\URL;

/**
 * Integration test: Fires up a server in the background and actually
 * tests protocol.
 */
class StompIntegrationTest extends \unittest\TestCase {
  const QUEUE = '/queue/test';

  protected static $serverProcess = null;
  protected static $bindAddress   = null;
  protected $fixture  = null;

  /**
   * Sets up test case
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

  /**
   * Connect and authenticate
   *
   * @return  peer.stomp.Connection
   */
  public function newConnection() {
    $this->fixture= new Connection(new URL('stomp://test:test@'.self::$bindAddress));
    $this->fixture->setTrace(\util\log\Logger::getInstance()->getCategory());
    $this->fixture->connect();
    return $this->fixture;
  }

  /**
   * Set up test: Disconnect
   */
  public function tearDown() {
    if ($this->fixture) {
      $this->fixture->disconnect();
    }
  }

  #[@test, @expect('peer.AuthenticationException')]
  public function login_with_invalid_credentials() {
    $conn= new Connection(new URL('stomp://unknownuser:invalidpass@'.self::$bindAddress));
    $conn->connect();
  }

  #[@test, @expect('peer.AuthenticationException')]
  public function login_without_credentials() {
    $conn= new Connection(new URL('stomp://'.self::$bindAddress));
    $conn->connect();
  }

  #[@test]
  public function send_subscribe_and_receive_sent_message() {
    $conn= $this->newConnection();
    $dest= $conn->getDestination(self::QUEUE);

    // Send a message
    $dest->send(new SendableMessage('This is a text message'));

    // Subscribe
    $messages= create('new util.collections.Vector<peer.stomp.Message>');
    $sub= $conn->subscribeTo(new Subscription($dest->getName(), function($message) use($messages) {
      $messages[]= $message;
      $message->ack();
    }));

    // Receive (using one second timeout)
    $this->assertTrue($conn->consume(1.0), 'consume');
    $this->assertEquals('This is a text message', $messages[0]->getBody());
  }

  #[@test]
  public function send_message() {
    $conn= $this->newConnection();
    $conn->getDestination(self::QUEUE)->send(new SendableMessage('This is a text message'));
  }

  #[@test]
  public function send_message_with_receipt() {
    $conn= $this->newConnection();

    $message= new SendableMessage('This is a text message');
    $message->addHeader('receipt', 'some-message-receipt');
    $response= $conn->getDestination(self::QUEUE)->send($message);

    $this->assertEquals('some-message-receipt', $response);
  }

  #[@test]
  public function subscribe_to_empty_queue() {
    $conn= $this->newConnection();
    $dest= $conn->getDestination(self::QUEUE);

    // Subscribe
    $messages= create('new util.collections.Vector<peer.stomp.Message>');
    $sub= $conn->subscribeTo(new Subscription($dest->getName(), function($message) use($messages) {
      $messages[]= $message;
      $message->ack();
    }));

    // Receive
    $conn->consume();
    $this->assertEquals(true, $messages->isEmpty());
  }
}
