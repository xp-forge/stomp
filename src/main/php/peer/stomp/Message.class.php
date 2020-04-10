<?php namespace peer\stomp;

use util\Objects;

/**
 * Message base class
 */
abstract class Message implements \lang\Value {
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
   * Get header
   *
   * @param  string $key
   * @return ?string
   */
  public function getHeader($key) {
    return $this->customHeader[$key] ?? null;
  }

  /**
   * Retrieve all headers
   * 
   * @return [:string]
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
    return sprintf(
      "%s@{\n".
      "  [  persistence ] %s\n".
      "  [ content-type ] %s\n".
      "  [         body ] %s\n".
      "  [      headers ] %s\n".
      "}",
      nameof($this),
      $this->persistence ? 'true' : 'false',
      $this->contentType,
      $this->body,
      Objects::stringOf($this->customHeader, '  ')
    );
  }

  /**
   * Retrieve hashcode
   * 
   * @return string
   */
  public function hashCode() {
    return 'M#'.md5($this->persistence.$this->body.$this->contenttype.serialize($this->customHeader));
  }

  /**
   * Compare
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $this === $value ? 0 : 1;
  }
}