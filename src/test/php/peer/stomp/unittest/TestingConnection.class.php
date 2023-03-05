<?php namespace peer\stomp\unittest;

use io\streams\{StringReader, StringWriter, MemoryInputStream, MemoryOutputStream};
use peer\URL;
use peer\stomp\Connection;

class TestingConnection extends Connection {
  public $response= '';
  public $in, $out, $sent;

  /** @param ?string|peer.URL $arg */
  public function __construct($arg= null) {
    if ($arg instanceof URL) {
      $url= $arg;
    } else {
      $url= new URL($arg ?? 'stomp://localhost');
    }

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
}