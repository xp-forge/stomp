<?php namespace peer\stomp;

use lang\Object;
use lang\Throwable;
use lang\IllegalArgumentException;

class Failover extends Object {
  private $pool;
  private $strategy;

  public function __construct(array $endpoints) {
    if (sizeof($endpoints) == 0) {
      throw new IllegalArgumentException('Failover expects at least 1 pool member, none given.');
    }

    $this->pool= $endpoints;
  }

  public static function using(array $endpoints) {
    return new self($endpoints);
  }

  public function byRandom() {
    $this->strategy= [$this, 'random'];
    return $this;
  }

  public function bySerial() {
    $this->strategy= [$this, 'serial'];
    return $this;
  }

  public function elect($callback) {
    call_user_func_array($this->strategy, [$this->pool, $callback]);
  }

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

        // No exception means success, just return from here
        $ret= $callback($member);

        // If return value was trueish, return it, otherwise
        // assume a failed connection
        if ($ret) {
          return $ret;
        }
      } catch (Throwable $t) {
        $lastException= $t;
      } finally {
        unset($pool[$idx]);
        $pool= array_values($pool);
      }
    }

    if ($lastException !== null) {
      throw $lastException;
    }

    return false;
  }

  private function serial(array $pool, $callback) {
    $lastException= null;

    foreach ($pool as $member) {
      try {
        $ret= $callback($member);
        if ($ret) {
          return $ret;
        }
      } catch (Throwable $t) {
        $lastException= $t;
      }
    }

    if ($lastException) {
      throw $lastException;
    }

    return false;
  }

  public function members() {
    return sizeof($this->pool);
  }
  
  public function member($pos= 0) {
    return $this->pool[$pos];
  }

  public function toString() {
    return sprintf('%s(%d endpoints){ %s }',
      nameof($this),
      sizeof($this->pool),
      implode(', ', $this->pool)
    );
  }
}