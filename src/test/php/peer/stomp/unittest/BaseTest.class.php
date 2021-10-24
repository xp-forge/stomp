<?php namespace peer\stomp\unittest;

use io\streams\{MemoryInputStream, MemoryOutputStream, StringReader, StringWriter};
use peer\URL;
use peer\stomp\Connection;

abstract class BaseTest extends \unittest\TestCase {
  protected $fixture= null;

  /**
   * Sets up unittest and creates fixture
   *
   */
  public function setUp() {
    $this->fixture= $this->newConnection(new URL('stomp://user:pass@localhost:61613'));
  }

  protected function newConnection(URL $url) {
    return new class($url) extends Connection {
      public $response = '';
      public $sent     = null;
      public $in       = null;
      public $out      = null;

      public function __construct($url) {
        parent::__construct($url);
        $this->_connect($url);    // FIXME: Required for unittest
      }

      public function _connect(URL $url) {
        $this->in= new StringReader(new MemoryInputStream($this->response));
        $this->out= new StringWriter(new MemoryOutputStream());
      }

      public function _disconnect() {
        $this->sent= $this->out->stream()->bytes();
        $this->in= null;
        $this->out= null;
      }

      public function setResponseBytes($s) {
        $this->in= new StringReader(new MemoryInputStream($s));
        $this->response= $s;
      }

      public function readSentBytes() {

        // Case of DISCONNECT
        if (null !== $this->sent) {
          $sent= $this->sent;
          $this->sent= null;
          return $sent;
        }

        return $this->out->stream()->bytes();
      }

      public function clearSentBytes() {
        $this->_connect(new URL());
        $this->sent= null;
      }
    };
  }
}