<?php namespace peer\stomp\frame;

use lang\Value;
use peer\stomp\Header;
use util\log\Traceable;

/**
 * Abstract frame base class
 *
 * @test  xp://peer.stomp.unittest.StompFrameTest
 * @test  xp://peer.stomp.unittest.FrameFromWireTest
 * @test  xp://peer.stomp.unittest.FrameToWireTest
 */
abstract class Frame implements Value, Traceable {
  protected $headers  = [];
  protected $body     = null;

  protected $cat      = null;

  /**
   * Retrieve frame command. Override this in derived implementations
   *
   * @return  string
   */
  public abstract function command();

  /**
   * Set trace
   *
   * @param   util.log.LogCategory cat
   */
  public function setTrace($cat) {
    $this->cat= $cat;
  }

  /**
   * Debug helper method
   *
   * @param var... $args
   */
  private function debug(... $args) {
    $this->cat && $this->cat->debug(substr(get_class($this), strlen(__NAMESPACE__) + 1), ...$args);
  }

  /**
   * Retrieve whether message requires immediate response
   *
   * @return  bool
   */
  public function requiresImmediateResponse() {
    return $this->hasHeader(Header::RECEIPT);
  }

  /**
   * Indicate whether to expect a RECEIPT frame after
   * sending or not.
   *
   * @param boolean $r
   */
  public function setWantReceipt($r= false) {
    if ($r) {
      $this->addHeader(Header::RECEIPT, $this->hashCode());
      return;
    }

    if (!$r) {
      $this->clearHeader(Header::RECEIPT);
    }
  }

  /**
   * Retrieve headers
   *
   * @return  <string,string>[]
   */
  public function getHeaders() {
    return $this->headers;
  }

  /**
   * Get header
   *
   * @param   string key
   * @return  string
   * @throws  lang.IllegalArgumentException if header does not exist
   */
  public function getHeader($key) {
    if (!isset($this->headers[$key])) throw new \lang\IllegalArgumentException(
      'No such header "'.$key.'"'
    );
    return $this->headers[$key];
  }

  /**
   * Add header
   *
   * @param   string key
   * @param   string value
   */
  public function addHeader($key, $value) {
    $this->headers[$key]= $value;
  }

  /**
   * Clear given header
   *
   * @param  string $key header name
   */
  public function clearHeader($key) {
    unset($this->headers[$key]);
  }

  /**
   * Check for header
   *
   * @param   string key
   * @return  bool
   */
  public function hasHeader($key) {
    return isset($this->headers[$key]);
  }

  /**
   * Retrieve body
   *
   * @return  string
   */
  public function getBody() {
    return $this->body;
  }

  /**
   * Set body
   *
   * @param   string data
   */
  public function setBody($data) {
    $this->body= $data;
  }

  /**
   * Read frame from wire
   *
   * @param   io.streams.InputStreamReader in
   */
  public function fromWire(\io\streams\InputStreamReader $in) {

    // Read headers. See https://stomp.github.io/stomp-specification-1.2.html#Value_Encoding
    $line= $in->readLine();
    while (0 !== strlen($line)) {
      $this->debug('<<<', $line);

      list($key, $value)= explode(':', $line, 2);
      $this->addHeader($key, strtr($value, ['\\\\' => '\\', '\c' => ':', '\r' => "\r", '\n' => "\n"]));

      // Next line
      $line= $in->readLine();
    }

    // Now, read payload
    if ($this->hasHeader(Header::CONTENTLENGTH)) {
      $this->debug('Reading ', $this->getHeader(Header::CONTENTLENGTH), 'bytes as indicated by content-length.');

      // If content-length is given, read that many bytes as body from
      // stream and assert that it is followed by a chr(0) byte.
      $length= (int)$this->getHeader(Header::CONTENTLENGTH);
      if ($length > 0) {
        $data= $in->read($length);
      } else {
        $data= '';
      }

      if ("\0" != $in->read(1)) throw new \peer\ProtocolException(
        'Expected chr(0) after frame w/ given content-length'
      );
    } else {
      $this->debug('Reading bytewise until \\0');

      // Read byte-wise until we find \0
      $data= '';
      do {
        if (null === ($c= $in->read(1))) throw new \peer\ProtocolException(
          'Received EOF before payload end delimiter \0\n'
        );
        $data.= $c;
      } while ("\0" !== $c);
    }

    $this->setBody(rtrim($data, "\n\0"));
  }

  /**
   * Write frame to stream
   *
   * @param   io.streams.OutputStreamWriter out
   */
  public function write(\io\streams\OutputStreamWriter $out) {
    $out->write($this->command()."\n");

    foreach ($this->getHeaders() as $key => $value) {
      $out->write($key.':'.strtr($value, ['\\' => '\\\\', ':' => '\c', "\r" => '\r', "\n" => '\n'])."\n");
    }

    $out->write("\n".$this->getBody().chr(0));
  }

  /**
   * Retrieve string representation
   *
   * @return  string
   */
  public function toString() {
    $s= nameof($this).'@('.$this->hashCode().") {\n";
    $s.= '  Stomp command=    "'.$this->command()."\"\n";

    foreach ($this->headers as $key => $value) {
      $s.= sprintf("  [%-15s] %s\n", $key, $value);
    }

    $s.= sprintf("  [%-15s] (%d bytes) %s\n",
      'body',
      strlen($this->getBody()),
      $this->getBody()
    );

    return $s.'}';
  }

  /**
   * Retrieve hashcode
   * 
   * @return string
   */
  public function hashCode() {
    return 'F#'.md5($this->body.serialize($this->headers));
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