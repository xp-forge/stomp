<?php namespace peer\stomp\unittest;

use peer\stomp\IConnection;
use lang\Object;
use peer\stomp\Transaction;
use peer\stomp\Subscription;
use lang\MethodNotImplementedException;
use peer\ConnectException;

class MockConnection extends Object implements IConnection {
  public $name = null;
  public $connect = null;

  public function __construct($name, $connect= true) {
    $this->name= $name;
    $this->connect= $connect;
  }

  public function url() {
    throw new MethodNotImplementedException('Not implemented in mock', __METHOD__);
  }

  public function recvFrame($timeout= 0.2) {
    throw new MethodNotImplementedException('Not implemented in mock', __METHOD__);
  }

  public function sendFrame(\peer\stomp\frame\Frame $frame) {
    throw new MethodNotImplementedException('Not implemented in mock', __METHOD__);
  }
  
  public function connect() {
    if ($this->connect) {
      return true;
    }

    throw new ConnectException('Connection "'.$this->name.'" not available.');
  }

  public function disconnect() {
    throw new MethodNotImplementedException('Not implemented in mock', __METHOD__);
  }

  public function begin(Transaction $t) {
    throw new MethodNotImplementedException('Not implemented in mock', __METHOD__);
  }

  public function subscribeTo(Subscription $s) {
    throw new MethodNotImplementedException('Not implemented in mock', __METHOD__);
  }

  public function subscriptionById($id) {
    throw new MethodNotImplementedException('Not implemented in mock', __METHOD__);
  }

  public function receive($timeout) {
    throw new MethodNotImplementedException('Not implemented in mock', __METHOD__);
  }

  public function consume($timeout) {
    throw new MethodNotImplementedException('Not implemented in mock', __METHOD__);
  }

  public function getDestination($name) {
    throw new MethodNotImplementedException('Not implemented in mock', __METHOD__);
  }

  public function toString() {
    return nameof($this);
  }
}