<?php
  uses(
    'unittest.TestCase',
    'org.codehaus.stomp.StompConnection',
    'util.log.Logger',
    'util.log.ColoredConsoleAppender',
    'org.codehaus.stomp.frame.MessageFrame'
  );

  class StompIntegrationTest extends TestCase {
    const QUEUE = '/queue/unittest';

    protected
      $fixture  = NULL;

    protected static
      $serverProcess = NULL,
      $bindAddress   = NULL;

    /**
     * Sets up test case
     *
     */
    #[@beforeClass]
    public static function startStompServer() {

      // Arguments to server process
      $args= array(
        'debugServerProtocolToFile' => NULL,   
      );

      // Start server process
      self::$serverProcess= Runtime::getInstance()->newInstance(
        NULL, 
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
        } catch (IllegalStateException $e) {
          $status.= $e->getMessage();
        }
        throw new PrerequisitesNotMetError('Cannot start STOMP server: '.$status, NULL);
      }
    }

    /**
     * Shut down FTP server
     *
     */
    #[@afterClass]
    public static function shutdownStompServer() {
      sscanf(self::$bindAddress, '%s:%d', $host, $port);

      // Tell the STOMP server to shut down
      try {
        $c= new StompConnection($host, $port);
        $c->connect('test', 'test');
        $c->sendFrame(newinstance('org.codehaus.stomp.frame.Frame', array(), '{
          public function command() { return "SHUTDOWN"; }
          public function requiresImmediateResponse() { return FALSE; }
        }'));
        $c->disconnect();
      } catch (Throwable $ignored) {
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
        throw new IllegalStateException($status);
      }
      self::$serverProcess->close();
    }

    #[@beforeClass]
    public static function logger() {
      // Logger::getInstance()->getCategory()->addAppender(new ColoredConsoleAppender());
    }

    public function setUp() {
      sscanf(self::$bindAddress, '%[^:]:%d', $host, $port);
      $this->fixture= new StompConnection($host, $port);
      $this->fixture->setTrace(Logger::getInstance()->getCategory());
      $this->fixture->connect('test', 'test');
    }

    public function tearDown() {
      $this->fixture->disconnect();
    }

    #[@test, @ignore, @expect('peer.AuthenticationException')]
    public function invalidCredentials() {
      $conn= new StompConnection('localhost', 61613);
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
      $this->assertTrue($message instanceof org·codehaus·stomp·frame·MessageFrame);
    }

    #[@test]
    public function receiveReceipt() {
      $frame= new org·codehaus·stomp·frame·SendFrame(self::QUEUE, 'body');
      $frame->addHeader('receipt', 'some-message-receipt');

      $response= $this->fixture->sendFrame($frame);
      $this->assertTrue($response instanceof org·codehaus·stomp·frame·ReceiptFrame);
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
?>
