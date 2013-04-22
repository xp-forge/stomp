<?php namespace org\codehaus\stomp\unittest;

  abstract class BaseTest extends \unittest\TestCase {
    protected $fixture= NULL;

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

        public function clearSentBytes() {
          $this->_connect();
          $this->sent= NULL;
        }
      }');
    }
  }
?>