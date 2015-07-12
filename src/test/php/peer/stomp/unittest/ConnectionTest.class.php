<?php namespace peer\stomp\unittest;

use peer\URL;
use peer\stomp\Connection;

/**
 * Tests STOMP connection class
 *
 * @see   xp://peer.stomp.Connection
 */
class ConnectionTest extends \unittest\TestCase {

  /** @return  var[] */
  protected function constructorArgs() {
    return array('stomp://localhost:61003', new URL('stomp://localhost:61003'));
  }

  #[@test, @values('constructorArgs')]
  public function can_create($arg) {
    new Connection($arg);
  }

  #[@test, @values('constructorArgs')]
  public function url_accessor_returns_url($arg) {
    $this->assertEquals(new URL('stomp://localhost:61003'), (new Connection($arg))->url());
  }

  #[@test, @values([null, 'localhost:61003']), @expect('lang.IllegalArgumentException')]
  public function invalid_url_given_to_constructor($arg) {
    new Connection($arg);
  }
}
