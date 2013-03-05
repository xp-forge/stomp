<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses(
    'util.log.Traceable',
    'peer.Socket',
    'peer.SocketInputStream',
    'peer.SocketOutputStream',
    'io.streams.MemoryOutputStream',
    'io.streams.TextReader',
    'io.streams.TextWriter',
    'peer.AuthenticationException',
    'peer.ProtocolException',
    'org.codehaus.stomp.AckMode',
    'org.codehaus.stomp.frame.LoginFrame',
    'org.codehaus.stomp.frame.SendFrame',
    'org.codehaus.stomp.frame.SubscribeFrame',
    'org.codehaus.stomp.frame.UnsubscribeFrame',
    'org.codehaus.stomp.frame.BeginFrame',
    'org.codehaus.stomp.frame.CommitFrame',
    'org.codehaus.stomp.frame.AbortFrame',
    'org.codehaus.stomp.frame.AckFrame',
    'org.codehaus.stomp.frame.DisconnectFrame'
  );

  /**
   * Low level API to the STOMP protocol
   *
   * @see   http://stomp.codehaus.org/Protocol
   * @test  xp://org.codehaus.stomp.unittest.StompTest
   */
  class StompConnection extends Object implements Traceable {
    protected
      $server = NULL,
      $port   = NULL;

    protected
      $socket = NULL,
      $in     = NULL,
      $out    = NULL;

    protected
      $cat    = NULL;

    /**
     * Constructor
     *
     * @param   string server
     * @param   int port
     */
    public function __construct($server, $port) {
      $this->server= $server;
      $this->port= $port;
    }

    /**
     * Set trace
     *
     * @param   util.log.LogCategory cat
     */
    public function setTrace($cat) {
      $this->cat= $cat;
    }

    /**
     * Connect to server
     *
     */
    protected function _connect() {
      $this->socket= new Socket($this->server, $this->port);
      $this->socket->connect();

      $this->in= new StringReader(new SocketInputStream($this->socket));
      $this->out= new StringWriter(new SocketOutputStream($this->socket));
    }

    /**
     * Disconnect from server
     *
     */
    protected function _disconnect() {
      $this->out= NULL;
      $this->in= NULL;
      $this->socket->close();
    }

    /**
     * Receive next frame, nonblocking
     *
     * @param   double timeout default 0.2
     * @return  org.codehaus.stomp.frame.Frame or NULL
     */
    public function recvFrame($timeout= 0.2) {

      // Check whether we can read, before we actually read...
      if ($this->socket instanceof Socket && !$this->socket->canRead($timeout)) {
        $this->cat && $this->cat->debug($this->getClassName(), '<<<', '0 bytes - reading no frame.');
        return NULL;
      }

      $line= $this->in->readLine();
      $this->cat && $this->cat->debug($this->getClassName(), '<<<', 'Have "'.trim($line).'" command.');

      if (0 == strlen($line)) throw new ProtocolException('Expected frame token, got "'.xp::stringOf($line).'"');

      $frame= XPClass::forName(sprintf('org.codehaus.stomp.frame.%sFrame', ucfirst(strtolower(trim($line)))))
        ->newInstance()
      ;
      $frame->fromWire($this->in);

      // According to the STOMP protocol, the NUL ("\0") delimiter may be followed
      // by any number of EOL ("\n") characters. Read them here but be careful not
      // to read across past a socket's current stream end!
      // FIXME: This conflicts with heart-beating, we might be swallowing that here
      // but not reacting correctly in other places!
      $c= '';
      while (
        ($this->socket instanceof Socket ? $this->socket->canRead(0.01) : $this->in->getStream()->available()) &&
        "\n" === ($c= $this->in->read(1))
      ) {
        // Skip
      }
      $f= $this->in->getClass()->getField('buf')->setAccessible(TRUE);
      $f->set($this->in, $c.$f->get($this->in));

      return $frame;
    }

    /**
     * Send a frame to server
     *
     */
    public function sendFrame(org�codehaus�stomp�frame�Frame $frame) {

      // Trace
      if ($this->cat) {
        $mo= new MemoryOutputStream();
        $frame->write(new StringWriter($mo));

        $this->cat->debug($this->getClassName(), '>>>', $mo->getBytes());
      }

      $frame->write($this->out);

      if ($frame->requiresImmediateResponse()) {
        return $this->recvFrame();
      }

      return NULL;
    }

    /**
     * Connect to server with given username and password
     *
     * @param   string user
     * @param   string pass
     * @return  bool
     * @throws  peer.AuthenticationException if login failed
     */
    public function connect($user, $pass) {
      $this->_connect();

      $frame= $this->sendFrame(new org�codehaus�stomp�frame�LoginFrame($user, $pass));
      if (!$frame instanceof org�codehaus�stomp�frame�Frame) {
        throw new ProtocolException('Did not receive frame, got: '.xp::stringOf($frame));
      }
      if (!$frame instanceof org�codehaus�stomp�frame�ConnectedFrame) {
        throw new AuthenticationException(
          'Could not log in to stomp broker "'.$this->server.':'.$this->port.'": Got "'.$frame->command().'" frame',
          $user,
          $pass
        );
      }

      return TRUE;
    }

    /**
     * Disconnect by sending disconnect frame
     *
     */
    public function disconnect() {

      // Bail out if not connected
      if (!$this->out instanceof OutputStreamWriter) return;

      // Send disconnect frame and exit
      create(new org�codehaus�stomp�frame�DisconnectFrame())->write($this->out);
      $this->_disconnect();
    }

    /**
     * Begin server transaction
     *
     * @param   string transaction
     */
    public function begin($transaction) {
      return $this->sendFrame(new org�codehaus�stomp�frame�BeginFrame($transaction));
    }

    /**
     * Abort transaction
     *
     * @param   string transaction
     */
    public function abort($transaction) {
      return $this->sendFrame(new org�codehaus�stomp�frame�AbortFrame($transaction));
    }

    /**
     * Commit transaction
     *
     * @param   string transaction
     */
    public function commit($transaction) {
      return $this->sendFrame(new org�codehaus�stomp�frame�CommitFrame($transaction));
    }

    /**
     * Send new message to destination
     *
     * @param   string destination
     * @param   string body
     * @param   [:string] headers
     */
    public function send($destination, $body, $headers= array()) {
      return $this->sendFrame(new org�codehaus�stomp�frame�SendFrame($destination, $body, $headers));
    }

    /**
     * Subscribe to destination
     *
     * @see     xp://org.codehaus.stomp.AckMode
     * @param   string destination
     * @param   string ackMode default 'auto'
     * @param   string selector default NULL
     */
    public function subscribe($destination, $ackMode= AckMode::AUTO, $selector= NULL) {
      return $this->sendFrame(new org�codehaus�stomp�frame�SubscribeFrame($destination, $ackMode, $selector));
    }

    /**
     * Acknowledge a message
     *
     * @param   string messageId
     */
    public function ack($messageId) {
      return $this->sendFrame(new org�codehaus�stomp�frame�AckFrame($messageId));
    }

    /**
     * Receive a message
     *
     * @param   double timeout default 0.2 pass NULL for no timeout
     * @return  org.codehaus.stomp.frame.Frame
     */
    public function receive($timeout= 0.2) {
      return $this->recvFrame($timeout);
    }
    
    /**
     * Creates a string representation of this object
     *
     * @return  string
     */
    public function toString() {
      return $this->getClassName().'(->'.$this->server.':'.$this->port.')';
    }
  }
?>
