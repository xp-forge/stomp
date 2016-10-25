<?php namespace peer\stomp;

use lang\IllegalArgumentException;
use lang\Object;
use lang\Throwable;
use peer\stomp\Subscription;
use peer\stomp\Transaction;

/**
 * HAConnection represents multiple Connection objects that
 * will be tried in random order; the first one available with
 * successful connection will prevail.
 *
 * All exposed API methods will be passed through to the actual
 * Connection object.
 */
class HAConnection extends Object implements IConnection {
  private $conn = null;
  private $pool = [];

  /**
   * Constructor
   *
   * @param   var[] $url either a URL object or a string or a IConnection object or a list thereof
   * @throws  lang.IllegalArgumentException if string given is unparseable
   */
  public function __construct($url) {
    if (!is_array($url)) {
      $url= [$url];
    }

    foreach ($url as $u) {
      if ($u instanceof IConnection) {
        $this->pool[]= $u;
        continue;
      }

      $this->pool[]= new Connection($u);
    }
    
    if (sizeof($this->pool) == 0) {
      throw new IllegalArgumentException('Must give at least one connection.');
    }

    // Initialize $this->conn with any pool member (first for simplicity)
    $this->conn= $this->pool[0];
  }

  /**
   * Elect the member
   *
   */
  private function elect() {
    // Shortcut special case
    if (sizeof($this->pool) == 1) {
      return $this->pool[0];
    }

    // Copy pool into working variable that will shrink
    // when the given member's unable to connect. 
    $pool= $this->pool;
    $lastException= null;

    // Shrink until no members left
    while (sizeof($pool) > 0) {

      // Choose a random member
      $idx= rand(0, sizeof($pool)- 1);
      $member= $pool[$idx];

      try {
        $member->connect();

        // All good at this point, we've got the connection
        $this->conn= $member;
        return;
      } catch (Throwable $t) {
        $lastException= $t;
        unset($pool[$idx]);
        $pool= array_values($pool);
      }
    }

    if ($lastException !== null) {
      throw $lastException;
    }
  }

  /** @return peer.stomp.IConnection Retrieve current Connection in use */
  public function conn() {
    return $this->conn;
  }

  /** @return int pool size */
  public function poolSize() {
    return sizeof($this->pool);
  }

  /** @return peer.URL */
  public function url() {
    return $this->conn->url();
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
    return $this->conn->recvFrame($timeout);
  }

  /**
   * Send a frame to server
   *
   * This is a low-level protocol function.
   *
   * @param   peer.stomp.frame.Frame frame
   * @return  peer.stomp.Frame or null
   */
  public function sendFrame(\peer\stomp\frame\Frame $frame) {
    return $this->conn->sendFrame($frame);
  }

  /**
   * Connect to server with given username and password; will try all available
   * connections in random order.
   *
   * @return  bool
   * @throws  peer.AuthenticationException if login failed
   */
  public function connect() {
    $this->elect();
    return true;
  }

  /**
   * Disconnect by sending disconnect frame
   *
   */
  public function disconnect() {
    return $this->conn->disconnect();
  }

  /**
   * Begin server transaction
   *
   * @param   peer.stomp.Transaction transaction
   * @return  peer.stomp.Transaction
   */
  public function begin(Transaction $t) {
    return $this->conn->begin(t);
  }

  /**
   * Create new subscription
   *
   * @param  peer.stomp.StompSubscription $subscription
   * @return peer.stomp.StompSubscription
   */
  public function subscribeTo(Subscription $s) {
    return $this->conn->subscribeTo($s);
  }

  /**
   * Retrieve an active subscription by its id.
   *
   * @param  string id
   * @return peer.stomp.Subscription
   * @throws peer.stomp.Exception if no subscription could be found.
   */
  public function subscriptionById($id) {
    return $this->conn->subscriptionById($id);
  }

  /**
   * Receive a message
   *
   * @param   double timeout default 0.2 pass null for no timeout
   * @return  peer.stomp.frame.Frame
   */
  public function receive($timeout) {
    return $this->conn->receive($timeout);
  }

  /**
   * Consume a message; delegates the handling to the corresponding
   * subscription.
   *
   * @param  float $timeout time to wait for new message
   * @return boolean whether a message was processed or not
   */
  public function consume($timeout) {
    return $this->conn->consume($timeout);
  }

  /**
   * Retrieve destination
   *
   * @param string name
   * @return peer.stomp.Destination
   */
  public function getDestination($name) {
    return $this->conn->getDestination($name);
  }

  /**
   * Creates a string representation of this object
   *
   * @return  string
   */
  public function toString() {
    return nameof($this).'(size='.$this->poolSize().'){'.$this->conn->toString().'}';
  }
}