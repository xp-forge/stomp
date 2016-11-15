<?php namespace peer\stomp;

use util\log\Logger;
use util\log\Traceable;
use peer\URL;
use peer\Socket;
use peer\SocketInputStream;
use peer\SocketOutputStream;
use peer\ProtocolException;
use peer\AuthenticationException;
use io\streams\MemoryOutputStream;
use io\streams\OutputStreamWriter;
use io\streams\StringReader;
use io\streams\StringWriter;
use peer\stomp\frame\Frame;
use peer\stomp\frame\LoginFrame;
use peer\stomp\frame\ConnectedFrame;
use peer\stomp\frame\DisconnectFrame;
use peer\stomp\frame\ReceiptFrame;
use peer\stomp\frame\ErrorFrame;
use peer\stomp\frame\MessageFrame;

/**
 * API to the STOMP protocol
 *
 * @see   http://stomp.codehaus.org/Protocol
 * @test  xp://peer.stomp.unittest.ConnectionTest
 * @test  xp://peer.stomp.unittest.StompTest
 */
class Connection extends \lang\Object implements Traceable {
  private $failover        = null;
  protected $url           = null;
  protected $socket        = null;
  protected $in            = null;
  protected $out           = null;
  protected $subscriptions = [];
  protected $cat           = null;

  /**
   * Constructor
   *
   * @param   var $url either a URL object or a string
   * @throws  lang.IllegalArgumentException if string given is unparseable
   */
  public function __construct($url) {
    if ($url instanceof Failover) {
      $this->failover= $url;
    } else {
      $this->failover= Failover::using([$url])->byRandom();
    }

    // Walk through all failover members, to check they all have valid URLs; by returning
    // false, indicate to failover to the next available member.
    // For BC reasons, if one connection has a log=<category> parameter, read it
    // and set it into this objects $cat member.
    $this->failover->elect(function($url) {
      $url= self::urlFrom($url);

      if (!$this->cat && $url->hasParam('log')) {
        $this->setTrace(Logger::getInstance()->getCategory($url->getParam('log')));
      }
      return false;
    });
  }

  private static function urlFrom($thing) {
    if ($thing instanceof URL) {
      return $thing;
    } else {
      try {
        return new URL((string)$thing);
      } catch (\lang\FormatException $e) {
        throw new \lang\IllegalArgumentException('Invalid URL given', $e);
      }
    }
  }

  /** @return peer.URL */
  public function url() {
    if ($this->url) {
      return $this->url;
    }

    return self::urlFrom($this->failover->member(0));
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
   * Helper method for logging
   * 
   */
  private function debug() {
    if ($this->cat) {
      $args= func_get_args();
      array_unshift($args, $this->getClass()->getSimpleName());
      call_user_func_array([$this->cat, 'debug'], $args);
    }
  }

  /**
   * Receive next frame, nonblocking
   *
   * This is a low-level protocol function.
   *
   * @param   double timeout default 0.2
   * @return  peer.stomp.frame.Frame or null
   */
  public function recvFrame($timeout= 0.2) {

    // Check whether we can read, before we actually read...
    if ($this->socket instanceof Socket && !$this->socket->canRead($timeout)) {
      $this->debug('<<<', '0 bytes - reading no frame.');
      return null;
    }

    do {
      $line= $this->in->readLine();
      if (null === $line) {
        $this->_disconnect();
        throw new ServerDisconnected('Got disconnected from '.$this->socket->toString());
      }
    } while ('' === $line);
    $this->debug('<<<', 'Have "'.trim($line).'" command');

    if (0 == strlen($line)) throw new ProtocolException('Expected frame token, got '.\xp::stringOf($line));

    $frame= $this->getClass()
      ->getPackage()
      ->getPackage('frame')
      ->loadClass(ucfirst(strtolower(trim($line))).'Frame')
      ->newInstance()
    ;
    $frame->setTrace($this->cat);
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
      $this->debug('~ ate a byte: '.\xp::stringOf($c));
    }

    $f= typeof($this->in)->getField('buf')->setAccessible(true);
    $f->set($this->in, $c.$f->get($this->in));

    return $frame;
  }

  /**
   * Send a frame to server
   *
   * This is a low-level protocol function.
   *
   * @param   peer.stomp.frame.Frame frame
   * @return  peer.stomp.Frame or null
   */
  public function sendFrame(Frame $frame) {

    // Trace
    if ($this->cat) {
      $mo= new MemoryOutputStream();
      $frame->write(new StringWriter($mo));

      $this->debug('>>>', $mo->getBytes());
    }

    $frame->write($this->out);

    if ($frame->requiresImmediateResponse()) {
      return $this->recvFrame();
    }

    return null;
  }

  /**
   * Connect to server
   *
   */
  protected function _connect(URL $url) {
    $this->socket= new Socket($url->getHost(), $url->getPort(61612));
    $this->socket->connect();

    $this->in= new StringReader(new SocketInputStream($this->socket));
    $this->out= new StringWriter(new SocketOutputStream($this->socket));
  }

  /**
   * Disconnect from server
   *
   */
  protected function _disconnect() {
    $this->out= null;
    $this->in= null;
    $this->socket->close();
  }

  private function _sendAuthenticateFrame(URL $url) {
    $frame= $this->sendFrame(new LoginFrame(
      $url->getUser(),
      $url->getPassword(),
      $url->getParam('vhost', $url->getHost()),
      $url->hasParam('versions') ? explode(',', $url->getParam('versions')) : ['1.0', '1.1']
    ));

    if (!$frame instanceof Frame) {
      throw new ProtocolException('Did not receive frame, got: '.\xp::stringOf($frame));
    }
    if ($frame instanceof ErrorFrame) {
      throw new AuthenticationException(
        'Could not establish connection to broker "'.$url->toString().'": '.$frame->getBody(),
        $url->getUser(), (strlen($url->getPassword() > 0) ? 'with password' : 'no password')
      );
    }
    if (!$frame instanceof ConnectedFrame) {
      throw new AuthenticationException(
        'Could not log in to stomp broker "'.$url->toString().'": Got "'.$frame->command().'" frame',
        $url->getUser(), (strlen($url->getPassword() > 0) ? 'with password' : 'no password')
      );
    }

    $this->debug('~ Connected to server; server '.($frame->getProtocolVersion()
      ? 'chose protocol version '.$frame->getProtocolVersion()
      : 'did not indicate protocol version'
    ));
  }

  /**
   * Connect to server with given username and password
   *
   * @return  bool
   * @throws  peer.AuthenticationException if login failed
   */
  public function connect() {
    $this->url= self::urlFrom($this->failover->elect(function($endpoint) {
      $url= self::urlFrom($endpoint);

      $this->_connect($url);
      $this->_sendAuthenticateFrame($url);

      return true;
    }));

    return true;
  }

  /**
   * Returns true if connection is established
   *
   * @return bool
   */
  public function isConnected() {
    return $this->socket != null && $this->socket->isConnected();
  }

  /**
   * Disconnect by sending disconnect frame
   *
   */
  public function disconnect() {

    // Bail out if not connected
    if (!$this->out instanceof OutputStreamWriter) return;

    // Send disconnect frame and exit
    (new DisconnectFrame())->write($this->out);
    $this->_disconnect();
  }

  /**
   * Begin server transaction
   *
   * @param   peer.stomp.Transaction transaction
   * @return  peer.stomp.Transaction
   */
  public function begin(Transaction $transaction) {
    $transaction->begin($this);
    return $transaction;
  }

  /**
   * Create new subscription
   *
   * @param  peer.stomp.StompSubscription $subscription
   * @return peer.stomp.StompSubscription
   */
  public function subscribeTo(Subscription $subscription) {
    $subscription->subscribe($this);
    $this->subscriptions[$subscription->getId()]= $subscription;
    return $subscription;
  }

  /**
   * Unsubscribe; to be called from Subscription directly,
   * should not be called directly.
   * 
   * @param  peer.stomp.Subscription $subscription
   */
  public function _unsubscribe(Subscription $subscription) {
    unset($this->subscriptions[$subscription->getId()]);
  }

  /**
   * Retrieve an active subscription by its id.
   * 
   * @param  string id
   * @return peer.stomp.Subscription
   * @throws peer.stomp.Exception if no subscription could be found.
   */
  public function subscriptionById($id) {
    if (!isset($this->subscriptions[$id])) {
      throw new Exception('No such subscription: "'.$id.'"');
    }

    return $this->subscriptions[$id];
  }

  /**
   * Receive a message
   *
   * @param   double timeout default 0.2 pass null for no timeout
   * @return  peer.stomp.frame.Frame
   */
  public function receive($timeout= 0.2) {
    $frame= $this->recvFrame($timeout);

    if ($frame instanceof ErrorFrame) {
      throw (new Exception($frame->getMessage()))->withFrame($frame);
    }

    if ($frame instanceof MessageFrame) {
      $msg= new ReceivedMessage();
      $msg->withFrame($frame, $this);
      return $msg;
    }

    return $frame;
  }

  /**
   * Consume a message; delegates the handling to the corresponding
   * subscription.
   * 
   * @param  float $timeout time to wait for new message
   * @return boolean whether a message was processed or not
   */
  public function consume($timeout= 0.2) {
    $message= $this->receive($timeout);

    if ($message instanceof ReceivedMessage) {
      $message->getSubscription()->process($message);
      return true;
    }

    return false;
  }

  /**
   * Retrieve destination
   *
   * @param string name
   * @return peer.stomp.Destination
   */
  public function getDestination($name) {
    return new Destination($name, $this);
  }

  /**
   * Creates a string representation of this object
   *
   * @return  string
   */
  public function toString() {
    return sprintf('%s (%s) { -> %s }',
      nameof($this),
      \xp::stringOf($this->failover),
      \xp::stringOf($this->url())
    );
  }
}
