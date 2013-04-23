<?php namespace org\codehaus\stomp;

abstract class Message extends \lang\Object {
  protected $destination  = NULL;
  protected $subscription = NULL;
  protected $messageId    = NULL;
  protected $contentType  = NULL;
  protected $body         = NULL;
  protected $persistence  = TRUE;
  protected $customHeader = array();

  public function __construct($body= NULL, $contentType= NULL) {
    if ($body) {
      $this->setBody($body, $contentType);
    }
  }

  public function getSubscription() {
    return $this->subscription;
  }

  public function setSubscription(Subscription $s) {
    $this->subscription= $s;
  }

  public function setDestination($destination) {
    $this->destination= $destination;
  }

  public function getDestination() {
    return $this->destination;
  }

  public function getMessageId() {
    return $this->messageId;
  }

  public function setMessageId($id) {
    $this->messageId= $id;
  }

  public function setBody($body, $contentType= NULL) {
    $this->body= $body;

    if ($contentType) {
      $this->setContentType($contentType);
    }
  }

  public function setContentType($c) {
    $this->contentType= $c;
  }

  public function getBody() {
    return $this->body;
  }

  public function getContentType() {
    return $this->contentType;
  }

  public function getPersistence() {
    return $this->persistence;
  }

  public function setPersistence($p) {
    $this->persistence= (bool)$p;
  }

  public function addHeader($name, $value) {
    $this->customHeader[$name]= $value;
  }

  public function getHeaders() {
    return $this->customHeader;
  }

  protected function assertConnection() {
    if (!$this->conn instanceof StompConnection) {
      throw new \lang\IllegalStateException('Cannot ack message without connection');
    }
  }

  public function toString() {
    $s= $this->getClassName().'('.$this->hashCode().") {\n";
    $s.= "  [  destination ] ".$this->getDestination()."\n";
    $s.= "  [ subscription ] ".\xp::stringOf($this->getSubscription())."\n";
    $s.= "  [         conn ] ".\xp::stringOf($this->conn)."\n";
    $s.= "  [  persistence ] ".\xp::stringOf($this->getPersistence())."\n";
    $s.= "  [ content-type ] ".$this->getContentType()."\n";
    $s.= "  [         body ] ".$this->getBody()."\n";
    $s.= "  [      headers ] ".\xp::stringOf($this->getHeaders())."\n";

    return $s.'}';
  }
}