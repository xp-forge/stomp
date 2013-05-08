<?php namespace org\codehaus\stomp;

use org\codehaus\stomp\frame\MessageFrame;
use org\codehaus\stomp\frame\AckFrame;
use org\codehaus\stomp\frame\NackFrame;

/**
 * Message retrieved from server
 * 
 */
class ReceivedMessage extends Message {
  protected $destination  = NULL;
  protected $subscription = NULL;

  /**
   * Fill message members from given frame.
   * 
   * @param  org.codehaus.stomp.frame.MessageFrame $frame
   * @param  org.codehaus.stomp.Connection $conn
   */
  public function withFrame(MessageFrame $frame, Connection $conn) {
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

  /**
   * Set destination
   *
   * @param org.codehaus.stomp.Destination destination
   */
  public function setDestination(Destination $destination) {
    $this->destination= $destination;
  }

  /**
   * Get destination
   * 
   * @return org.codehaus.stomp.Destination
   */
  public function getDestination() {
    return $this->destination;
  }

  /**
   * Get subscription
   * 
   * @return org.codehaus.stomp.Subscription
   */
  public function getSubscription() {
    return $this->subscription;
  }

  /**
   * Set subscription
   * 
   * @param org.codehaus.stomp.Subscription $s
   */
  public function setSubscription(Subscription $s) {
    $this->subscription= $s;
  }

  /**
   * Helper method to assure message has a connection
   * 
   * @throws lang.IllegalStateException If no connection set
   */
  protected function assertConnection() {
    if (!$this->destination instanceof Destination) {
      throw new \lang\IllegalStateException('Cannot ack message without connection');
    }
  }

  /**
   * Acknowledge given message
   * 
   * @param  org.codehaus.stomp.Transaction $t
   */
  public function ack(Transaction $t= NULL) {
    $this->assertConnection();
    $frame= new AckFrame($this->getMessageId(), $this->getSubscription()->getId());
    if ($t) {
      $frame->setTransaction($t->getName());
    }
    $this->getDestination()->getConnection()->sendFrame($frame);
  }

  /**
   * Reject given message
   * 
   * @param  org.codehaus.stomp.Transaction $t
   */
  public function nack(Transaction $t= NULL) {
    $this->assertConnection();
    $frame= new NackFrame($this->getMessageId(), $this->getSubscription()->getId());
    if ($t) {
      $frame->setTransaction($t->getName());
    }
    $this->getDestination()->getConnection()->sendFrame($frame);
  }

  /**
   * Determine whether message is ackable
   * 
   * @return boolean
   */
  public function ackable() {
    return in_array($this->getSubscription()->getAckMode(), array(
      \org\codehaus\stomp\AckMode::CLIENT,
      \org\codehaus\stomp\AckMode::INDIVIDUAL
    ));
  }

  /**
   * 
   * @return [type]
   */
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
