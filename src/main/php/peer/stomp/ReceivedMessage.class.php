<?php namespace peer\stomp;

use peer\stomp\frame\{AckFrame, MessageFrame, NackFrame};
use util\Objects;

/**
 * Message retrieved from server
 */
class ReceivedMessage extends Message {
  protected $destination  = null;
  protected $subscription = null;

  /**
   * Fill message members from given frame.
   * 
   * @param  peer.stomp.frame.MessageFrame $frame
   * @param  peer.stomp.Connection $conn
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

    $this->setPersistence(false);
    if ($frame->hasHeader(Header::PERSISTENCE)) {
      $this->setPersistence('true' === $frame->getHeader(Header::PERSISTENCE));
    }

    $skipHeaders= [
      Header::DESTINATION   => true,
      Header::MESSAGEID     => true,
      Header::CONTENTTYPE   => true,
      Header::SUBSCRIPTION  => true,
      Header::CONTENTLENGTH => true
    ];

    foreach ($frame->getHeaders() as $name => $value) {
      if (isset($skipHeaders[$name])) continue;

      $this->addHeader($name, $value);
    }

    $this->body= $frame->getBody();
  }

  /**
   * Set destination
   *
   * @param peer.stomp.Destination destination
   */
  public function setDestination(Destination $destination) {
    $this->destination= $destination;
  }

  /**
   * Get destination
   * 
   * @return peer.stomp.Destination
   */
  public function getDestination() {
    return $this->destination;
  }

  /**
   * Get subscription
   * 
   * @return peer.stomp.Subscription
   */
  public function getSubscription() {
    return $this->subscription;
  }

  /**
   * Set subscription
   * 
   * @param peer.stomp.Subscription $s
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
   * @param  peer.stomp.Transaction $t
   */
  public function ack(Transaction $t= null) {
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
   * @param  peer.stomp.Transaction $t
   */
  public function nack(Transaction $t= null) {
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
    return in_array($this->getSubscription()->getAckMode(), [
      \peer\stomp\AckMode::CLIENT,
      \peer\stomp\AckMode::INDIVIDUAL
    ]);
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
    $s= nameof($this).'('.$this->hashCode().") {\n";
    $s.= "  [  destination ] ".Objects::stringOf($this->getDestination(), '  ')."\n";
    $s.= "  [ subscription ] ".Objects::stringOf($this->getSubscription(), '  ')."\n";
    $s.= "  [  persistence ] ".Objects::stringOf($this->getPersistence(), '  ')."\n";
    $s.= "  [ content-type ] ".$this->getContentType()."\n";
    $s.= "  [      headers ] ".Objects::stringOf($this->getHeaders(), '  ')."\n";
    $s.= "  [         body ] ".$this->getBody()."\n";

    return $s.'}';
  }
}