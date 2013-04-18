<?php
/*
 * This class is part of the XP Framework
 *
 */

  uses(
    'unittest.TestCase',
    'org.codehaus.stomp.StompSubscription'
  );

  class StompSubscriptionTest extends TestCase {

    /**
     * Sets up unittest and creates fixture
     *
     */
    public function setUp() {
      $this->fixture= newinstance('org.codehaus.stomp.StompConnection', array('localhost', 61616), '{
        protected $response= "";
        protected $sent= NULL;

        public function __construct($server, $port) {
          parent::__construct($server, $port);

          // FIXME: Required for unittest
          $this->_connect();
        }

        protected function _connect() {
          $this->in= new StringReader(new MemoryInputStream($this->response));
          $this->out= new StringWriter(new MemoryOutputStream());
        }

        protected function _disconnect() {
          $this->sent= $this->out->getStream()->getBytes();
          $this->in= NULL;
          $this->out= NULL;
        }

        public function setResponseBytes($s) {
          $this->in= new StringReader(new MemoryInputStream($s));
          $this->response= $s;
        }

        public function readSentBytes() {

          // Case of DISCONNECT
          if (NULL !== $this->sent) {
            $sent= $this->sent;
            $this->sent= NULL;
            return $sent;
          }

          return $this->out->getStream()->getBytes();
        }
      }');
    }

    /**
     * Test
     *
     */
    #[@test]
    public function create() {
      new StompSubscription('/queue/foo');
    }

    /**
     * Test
     *
     */
    #[@test]
    public function subscribe() {
      $subscription= $this->fixture->subscribe(new StompSubscription('/queue/foo'));

      $this->assertEquals("SUBSCRIBE\n".
        "destination:/queue/foo\n".
        "ack:client-individual\n".
        "id:".$subscription->getId()."\n".
        "\n\0",
        $this->fixture->readSentBytes()
      );
    }
  }
?>