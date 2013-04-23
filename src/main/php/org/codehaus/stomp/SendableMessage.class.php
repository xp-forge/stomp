<?php namespace org\codehaus\stomp;

class SendableMessage extends Message {
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
}