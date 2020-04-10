<?php namespace peer\stomp;

use peer\stomp\frame\SendFrame;

/**
 * Sendable message
 */
class SendableMessage extends Message {

  /**
   * Retrieve SEND frame for message
   * 
   * @param  peer.stomp.Destination $dest
   * @return peer.stomp.frame.SendFrame
   */
  public function toFrame(Destination $dest) {
    $headers= [];
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

    return new SendFrame(
      $dest->getName(),
      $this->getBody(),
      $headers
    );
  }
}