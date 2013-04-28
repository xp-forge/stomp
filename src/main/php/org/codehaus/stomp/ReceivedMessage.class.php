<?php namespace org\codehaus\stomp;

class ReceivedMessage extends Message {
  protected $destination  = NULL;
  protected $subscription = NULL;

  public function withFrame(frame\MessageFrame $frame, Connection $conn) {
    $this->frame= $frame;
    $this->setDestination($conn->getDestination($frame->getHeader(Header::DESTINATION)));

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
  }
  
  public function setDestination(Destination $destination) {
    $this->destination= $destination;
  }

  public function getDestination() {
    return $this->destination;
  }

  public function getSubscription() {
    return $this->subscription;
  }

  public function setSubscription(Subscription $s) {
    $this->subscription= $s;
  }

  protected function assertConnection() {
    if (!$this->destination instanceof Destination) {
      throw new \lang\IllegalStateException('Cannot ack message without connection');
    }
  }

  public function ack(Transaction $t= NULL) {
    $this->assertConnection();
    $frame= new frame\AckFrame($this->getMessageId(), $this->getSubscription()->getId());
    if ($t) {
      $frame->setTransaction($t->getName());
    }
    $this->getDestination()->getConnection()->sendFrame($frame);
  }

  public function nack(Transaction $t= NULL) {
    $this->assertConnection();
    $frame= new frame\NackFrame($this->getMessageId(), $this->getSubscription()->getId());
    if ($t) {
      $frame->setTransaction($t->getName());
    }
    $this->getDestination()->getConnection()->sendFrame($frame);
  }

  public function toSendable() {
    $message= new SendableMessage($this->getBody(), $this->getContentType());
    $message->setMessageId($this->getMessageId());
    $message->setPersistence($this->getPersistence());
    foreach ($this->getHeaders() as $name => $value) {
      $message->addHeader($name, $value);
    }

    return $message;
  }

  public function toString() {
    $s= $this->getClassName().'('.$this->hashCode().") {\n";
    $s.= "  [  destination ] ".\xp::stringOf($this->getDestination(), '  ')."\n";
    $s.= "  [ subscription ] ".\xp::stringOf($this->getSubscription(), '  ')."\n";
    $s.= "  [  persistence ] ".\xp::stringOf($this->getPersistence(), '  ')."\n";
    $s.= "  [ content-type ] ".$this->getContentType()."\n";
    $s.= "  [      headers ] ".\xp::stringOf($this->getHeaders(), '  ')."\n";
    $s.= "  [         body ] ".$this->getBody()."\n";

    return $s.'}';
  }
}
