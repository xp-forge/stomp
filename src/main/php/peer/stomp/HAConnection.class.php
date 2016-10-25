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

  public function url() {
    return $this->conn->url();
  }

  public function recvFrame($timeout= 0.2) {
    return $this->conn->recvFrame($timeout);
  }

  public function sendFrame(\peer\stomp\frame\Frame $frame) {
    return $this->conn->sendFrame($frame);
  }

  public function connect() {
    $this->elect();
    return true;
  }

  public function disconnect() {
    return $this->conn->disconnect();
  }

  public function begin(Transaction $t) {
    return $this->conn->begin(t);
  }

  public function subscribeTo(Subscription $s) {
    return $this->conn->subscribeTo($s);
  }

  public function subscriptionById($id) {
    return $this->conn->subscriptionById($id);
  }

  public function receive($timeout) {
    return $this->conn->receive($timeout);
  }

  public function consume($timeout) {
    return $this->conn->consume($timeout);
  }

  public function getDestination($name) {
    return $this->conn->getDestination($name);
  }

  public function toString() {
    return nameof($this).'(size='.$this->poolSize().'){'.$this->conn->toString().'}';
  }
}