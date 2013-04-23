<?php namespace org\codehaus\stomp;

class ReceivedMessage extends Message {

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

  public function toSendable() {
    $message= new SendableMessage($this->getBody(), $this->getContentType());
    $message->setMessageId($this->getMessageId());
    $message->setPersistence($this->getPersistence());
    foreach ($this->getHeaders() as $name => $value) {
      $message->addHeader($name, $value);
    }

    return $message;
  }
}
?>