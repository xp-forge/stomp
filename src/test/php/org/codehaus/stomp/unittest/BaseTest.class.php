<?php namespace org\codehaus\stomp\unittest;

abstract class BaseTest extends \unittest\TestCase {
  protected $fixture= NULL;

  /**
   * Sets up unittest and creates fixture
   *
   */
  public function setUp() {
    $this->fixture= $this->newConnection(new \peer\URL('stomp://user:pass@localhost:61613'));
  }

  protected function newConnection(\peer\URL $url) {
    return newinstance('peer.stomp.Connection', array($url), '{
      protected $response= "";
      protected $sent= NULL;

      public function __construct(\\peer\\URL $url) {
        parent::__construct($url);

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
