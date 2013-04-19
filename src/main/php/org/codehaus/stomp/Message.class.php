<?php namespace org\codehaus\stomp;

  class Message extends \lang\Object {
    protected $destination  = NULL;
    protected $subscription = NULL;
    protected $messageId    = NULL;
    protected $contentType  = NULL;
    protected $body         = NULL;
    protected $frame        = NULL;

    public function __construct() {
      
    }

    public function withFrame(frame\MessageFrame $frame) {
      $this->frame= $frame;
      $this->destination= $frame->getHeader(Header::DESTINATION);
      $this->messageId= $frame->getHeader(Header::MESSAGEID);

      if ($frame->hasHeader(Header::CONTENTTYPE)) {
        $this->contentType= $frame->getHeader(Header::CONTENTTYPE);
      }

      $this->body= $frame->getBody();
    }
  }
?>