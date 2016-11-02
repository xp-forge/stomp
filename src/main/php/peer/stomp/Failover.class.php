<?php namespace peer\stomp;

use lang\Object;
use lang\Throwable;
use lang\IllegalArgumentException;

/**
 * Failover election strategy class
 *
 * This class contains algorithms to elect an endpoint (or, really, anything) from a list
 * in different ways.
 *
 * Example:
 * ```
 * $conn= new \peer\stomp\Connection(Failover::using(['stomp://localhost/', 'stomp://other.example.com'])->byRandom()));
 * ```
 *
 */
class Failover extends Object {
  private $pool;
  private $strategy;

  /**
   * Constructor
   *
   * @param  var[] endpoints must not be empty list
   */
  public function __construct(array $endpoints) {
    if (empty($endpoints)) {
      throw new IllegalArgumentException('Failover expects at least 1 pool member, none given.');
    }

    $this->pool= $endpoints;
  }

  /**
   * Fluent interface factory method
   *
   * @return self
   */
  public static function using(array $endpoints) {
    return new self($endpoints);
  }

  /**
   * Select `byRandom` election algorithm
   *
   * @return self
   */
  public function byRandom() {
    $this->strategy= [$this, 'random'];
    return $this;
  }

  /**
   * Select `bySerial` election algorithm
   *
   * @return self
   */
  public function bySerial() {
    $this->strategy= [$this, 'serial'];
    return $this;
  }

  /**
   * Perform election
   *
   * @return var elected member
   */
  public function elect($callback) {
    return call_user_func_array($this->strategy, [$this->pool, $callback]);
  }

  /**
   * Random election
   *
   * @param   var[] pool
   * @param   callable callback
   * @return  var
   * @throws  lang.Throwable
   */
  private function random(array $pool, $callback) {

    // Shortcut special case
    if (sizeof($pool) == 1) {
      $callback($pool[0]);
      return $pool[0];
    }

    // Copy pool into working variable that will shrink
    // when the given member's unable to connect. 
    $lastException= null;

    // Shrink until no members left
    while (sizeof($pool) > 0) {

      // Choose a random member
      $idx= rand(0, sizeof($pool)- 1);
      $member= $pool[$idx];

      try {
        // A trueish return value from the callback indicates successful exection, so
        // elect this member by returning it to the caller
        if ($callback($member)) {
          return $member;
        }
      } catch (Throwable $t) {
        $lastException= $t;
      }

      unset($pool[$idx]);
      $pool= array_values($pool);
    }

    if ($lastException !== null) {
      throw $lastException;
    }

    return null;
  }

  /**
   * Serial election
   *
   * @param   var[] pool
   * @param   callable callback
   * @return  var
   * @throws  lang.Throwable
   */
  private function serial(array $pool, $callback) {
    $lastException= null;

    foreach ($pool as $member) {
      try {
        if ($callback($member)) {
          return $member;
        }
      } catch (Throwable $t) {
        $lastException= $t;
      }
    }

    if ($lastException) {
      throw $lastException;
    }

    return null;
  }

  /**
   * Retrieve all members
   *
   * @return var[]
   */
  public function members() {
    return sizeof($this->pool);
  }
  
  /**
   * Retrieve member at position
   *
   * @param int pos
   * @return var
   */
  public function member($pos= 0) {
    return $this->pool[$pos];
  }

  /**
   * Get string representation
   *
   */
  public function toString() {
    return sprintf('%s { %d endpoints, %s election }',
      nameof($this),
      sizeof($this->pool),
      (is_array($this->strategy) ? $this->strategy[1] : 'custom')
    );
  }
}