<?php namespace org\codehaus\stomp\frame;

  use \org\codehaus\stomp\Header;

  /**
   * Abstract frame base class
   *
   * @test  xp://org.codehaus.stomp.unittest.StompFrameTest
   */
  abstract class Frame extends \lang\Object {
    protected
      $headers  = array(),
      $body     = NULL;

    /**
     * Retrieve frame command. Override this in derived implementations
     *
     * @return  string
     */
    public abstract function command();

    /**
     * Retrieve whether message requires immediate response
     *
     * @return  bool
     */
    public function requiresImmediateResponse() {
      return $this->hasHeader(Header::RECEIPT);
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

      // Read headers
      $line= $in->readLine();
      while (0 != strlen($line)) {
        list($key, $value)= explode(':', $line, 2);
        $this->addHeader($key, $value);

        // Next line
        $line= $in->readLine();
      }

      // Now, read payload
      if ($this->hasHeader(Header::CONTENTLENGTH)) {

        // If content-length is given, read that many bytes as body from
        // stream and assert that it is followed by a chr(0) byte.
        $data= $in->read($this->getHeader(Header::CONTENTLENGTH));

        if ("\0" != $in->read(1)) throw new \peer\ProtocolException(
          'Expected chr(0) after frame w/ given content-length'
        );
      } else {
      
        // Read byte-wise until we find \0
        $data= '';
        do {
          if (NULL === ($c= $in->read(1))) throw new \peer\ProtocolException(
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
        $out->write($key.':'.$value."\n");
      }

      $out->write("\n".$this->getBody().chr(0));
    }

    /**
     * Retrieve string representation
     *
     * @return  string
     */
    public function toString() {
      $s= $this->getClassName().'@('.$this->hashCode().") {\n";
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
  }
?>
