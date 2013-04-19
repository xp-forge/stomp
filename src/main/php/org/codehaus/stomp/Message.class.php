<?php namespace org\codehaus\stomp;

class Message extends \lang\Object {
  protected $destination  = NULL;
  protected $subscription = NULL;
  protected $messageId    = NULL;
  protected $contentType  = NULL;
  protected $body         = NULL;
  protected $frame        = NULL;

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

    $this->body= $frame->getBody();
  }

  public function getSubscription() {
    return $this->subscription;
  }

  public function setSubscription(Subscription $s) {
    $this->subscription= $s;
  }
}