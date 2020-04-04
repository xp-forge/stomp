<?php namespace peer\stomp\unittest;

use io\streams\MemoryInputStream;
use io\streams\MemoryOutputStream;
use io\streams\StringReader;
use io\streams\StringWriter;
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
    return newinstance(Connection::class, [$url], [
      'response' => '',
      'sent'     => null,
      'in'       => null,
      'out'      => null,

      '__construct' => function($url) {
        parent::__construct($url);
        $this->_connect($url);    // FIXME: Required for unittest
      },

      '_connect' => function(URL $url) {
        $this->in= new StringReader(new MemoryInputStream($this->response));
        $this->out= new StringWriter(new MemoryOutputStream());
      },

      '_disconnect' => function() {
        $this->sent= $this->out->getStream()->getBytes();
        $this->in= null;
        $this->out= null;
      },

      'setResponseBytes' => function($s) {
        $this->in= new StringReader(new MemoryInputStream($s));
        $this->response= $s;
      },

      'readSentBytes' => function() {

        // Case of DISCONNECT
        if (null !== $this->sent) {
          $sent= $this->sent;
          $this->sent= null;
          return $sent;
        }

        return $this->out->getStream()->getBytes();
      },

      'clearSentBytes' => function() {
        $this->_connect(new URL());
        $this->sent= null;
      }
    ]);
  }
}
