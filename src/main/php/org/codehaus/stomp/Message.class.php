<?php namespace org\codehaus\stomp;

class Message extends \lang\Object {
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

  public function withFrame(frame\MessageFrame $frame, StompConnection $conn) {
    $this->frame= $frame;
    $this->destination= $frame->getHeader(Header::DESTINATION);
    $this->messageId= $frame->getHeader(Header::MESSAGEID);

    if ($frame->hasHeader(Header::CONTENTTYPE)) {
      $this->contentType= $frame->getHeader(Header::CONTENTTYPE);
    }

    if ($frame->hasHeader(Header::SUBSCRIPTION)) {
      $this->setSubscription($conn->subscriptionById($frame->getHeader(Header::SUBSCRIPTION)));
    }

    $this->setPersistence(FALSE);
    if ($frame->hasHeader(Header::PERSISTENCE)) {
      $this->setPersistence('true' === $frame->getHeader(Header::PERSISTENCE));
    }

    $skipHeaders= array(
      Header::DESTINATION   => TRUE,
      Header::MESSAGEID     => TRUE,
      Header::CONTENTTYPE   => TRUE,
      Header::SUBSCRIPTION  => TRUE,
      Header::CONTENTLENGTH => TRUE
    );

    foreach ($frame->getHeaders() as $name => $value) {
      if (isset($skipHeaders[$name])) continue;

      $this->addHeader($name, $value);
    }

    $this->body= $frame->getBody();
    $this->conn= $conn;
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

  public function ack(Transaction $t= NULL) {
    $this->assertConnection();
    $frame= new frame\AckFrame($this->getMessageId());
    if ($t) {
      $frame->setTransaction($t->getName());
    }
    $this->conn->sendFrame($frame);
  }

  public function nack(Transaction $t= NULL) {
    $this->assertConnection();
    $frame= new frame\NackFrame($this->getMessageId());
    if ($t) {
      $frame->setTransaction($t->getName());
    }
    $this->conn->sendFrame($frame);
  }

  protected function assertConnection() {
    if (!$this->conn instanceof StompConnection) {
      throw new \lang\IllegalStateException('Cannot ack message without connection');
    }
  }

  public function send(StompConnection $conn) {
    $headers= array();
    if ($this->getMessageId()) {
      $headers[Header::MESSAGEID]= $this->getMessageId();
    }

    $headers[Header::CONTENTLENGTH]= 0;  // Will be auto-calculated

    if ($this->getContentType()) {
      $headers[Header::CONTENTTYPE]= $this->getContentType();
    }

    if ($this->getPersistence()) {
      $headers[Header::PERSISTENCE]= 'true';
    }

    $headers= array_merge($headers, $this->getHeaders());

    $frame= new frame\SendFrame(
      $this->getDestination(),
      $this->getBody(),
      $headers
    );


    $conn->sendFrame($frame);
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