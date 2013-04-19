<?php namespace org\codehaus\stomp;

  class Message extends \lang\Object {
    protected $destination  = NULL;
    protected $subscription = NULL;
    protected $messageId    = NULL;
    protected $contentType  = NULL;
    protected $body         = NULL;

    public function __construct() {
      
    }
  }
?>