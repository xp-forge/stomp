<?php namespace peer\stomp;

/**
 * Message base class
 */
abstract class Message extends \lang\Object {
  protected $messageId    = null;
  protected $contentType  = null;
  protected $body         = null;
  protected $persistence  = true;
  protected $customHeader = [];

  /**
   * Constructor
   * 
   * @param string $body 
   * @param string $contentType 
   */
  public function __construct($body= null, $contentType= null) {
    if ($body) {
      $this->setBody($body, $contentType);
    }
  }

  /**
   * Get message id
   * 
   * @return int
   */
  public function getMessageId() {
    return $this->messageId;
  }

  /**
   * Set message id
   * 
   * @param int $id 
   */
  public function setMessageId($id) {
    $this->messageId= $id;
  }

  /**
   * Set body
   * 
   * @param string $body 
   * @param string $contentType 
   */
  public function setBody($body, $contentType= null) {
    $this->body= $body;

    if ($contentType) {
      $this->setContentType($contentType);
    }
  }

  /**
   * Set content type
   * 
   * @param string $c 
   */
  public function setContentType($c) {
    $this->contentType= $c;
  }

  /**
   * Retrieve body
   * 
   * @return string
   */
  public function getBody() {
    return $this->body;
  }

  /**
   * Retrieve contenttype
   * 
   * @return string
   */
  public function getContentType() {
    return $this->contentType;
  }

  /**
   * Retrieve persistence value
   * 
   * @return bool
   */
  public function getPersistence() {
    return $this->persistence;
  }

  /**
   * Set persistence value
   * 
   * @param bool $p 
   */
  public function setPersistence($p) {
    $this->persistence= (bool)$p;
  }

  /**
   * Set a given header to given value
   * 
   * @param string $name 
   * @param string $value
   */
  public function addHeader($name, $value) {
    $this->customHeader[$name]= $value;
  }

  /**
   * Retrieve all headers
   * 
   * @return <string,string>[]
   */
  public function getHeaders() {
    return $this->customHeader;
  }

  /**
   * Retrieve string representation
   * 
   * @return string
   */
  public function toString() {
    $s= nameof($this).'('.$this->hashCode().") {\n";
    $s.= "  [  persistence ] ".\xp::stringOf($this->getPersistence())."\n";
    $s.= "  [ content-type ] ".$this->getContentType()."\n";
    $s.= "  [         body ] ".$this->getBody()."\n";
    $s.= "  [      headers ] ".\xp::stringOf($this->getHeaders(), '  ')."\n";

    return $s.'}';
  }
}