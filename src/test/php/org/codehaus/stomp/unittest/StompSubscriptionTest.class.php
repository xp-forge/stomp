<?php namespace org\codehaus\stomp\unittest;

  use \org\codehaus\stomp\Subscription;

  class StompSubscriptionTest extends \unittest\TestCase {

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
          $this->in= new \\io\\streams\\StringReader(new \\io\\streams\\MemoryInputStream($this->response));
          $this->out= new \\io\\streams\\StringWriter(new \\io\\streams\\MemoryOutputStream());
        }

        protected function _disconnect() {
          $this->sent= $this->out->getStream()->getBytes();
          $this->in= NULL;
          $this->out= NULL;
        }

        public function setResponseBytes($s) {
          $this->in= new \\io\\streams\\StringReader(new \\io\\streams\\MemoryInputStream($s));
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
      new Subscription('/queue/foo');
    }

    /**
     * Test
     *
     */
    #[@test]
    public function subscribe() {
      $subscription= $this->fixture->subscribe(new Subscription('/queue/foo'));

      $this->assertEquals("SUBSCRIBE\n".
        "destination:/queue/foo\n".
        "ack:client-individual\n".
        "id:".$subscription->getId()."\n".
        "\n\0",
        $this->fixture->readSentBytes()
      );
    }

    /**
     * Test
     *
     */
    #[@test, @expect('lang.IllegalStateException')]
    public function unsubscribe_not_possible_when_not_subscribed() {
      create(new Subscription('foo'))->unsubscribe();
    }

    /**
     * Test
     *
     */
    #[@test]
    public function unsubscribe() {
      $subscription= $this->fixture->subscribe(new Subscription('/queue/foo'));
      $id= $subscription->getId();

      $subscription->unsubscribe();

      $this->assertEquals("SUBSCRIBE\n".
        "destination:/queue/foo\n".
        "ack:client-individual\n".
        "id:".$id."\n".
        "\n\0".
        "UNSUBSCRIBE\n".
        "id:".$id."\n".
        "\n\0",
        $this->fixture->readSentBytes()
      );
    }

    protected function createSubscription() {
      $s= $this->fixture->subscribe(new Subscription('/queue/foo'));
      return $s->getId();
    }

    /**
     * Test
     *
     */
    #[@test]
    public function destructor_removes_subscription() {
      $id= $this->createSubscription();

      $this->assertEquals("SUBSCRIBE\n".
        "destination:/queue/foo\n".
        "ack:client-individual\n".
        "id:".$id."\n".
        "\n\0".
        "UNSUBSCRIBE\n".
        "id:".$id."\n".
        "\n\0",
        $this->fixture->readSentBytes()
      );
    }
  }
?>