<?php namespace peer\stomp\unittest;

use lang\IllegalArgumentException;
use peer\stomp\{Connection, Failover};
use peer\{URL, Socket, SSLSocket};
use unittest\{Expect, Test, Values};

/**
 * Tests STOMP connection class
 *
 * @see   xp://peer.stomp.Connection
 */
class ConnectionTest extends \unittest\TestCase {

  /** @return  var[] */
  protected function constructorArgs() {
    return ['stomp://localhost:61003', new URL('stomp://localhost:61003')];
  }

  #[Test, Values('constructorArgs')]
  public function can_create($arg) {
    new Connection($arg);
  }

  #[Test, Values('constructorArgs')]
  public function url_accessor_returns_url($arg) {
    $this->assertEquals(new URL('stomp://localhost:61003'), (new Connection($arg))->url());
  }

  #[Test, Values([null, 'localhost:61003']), Expect(IllegalArgumentException::class)]
  public function invalid_url_given_to_constructor($arg) {
    new Connection($arg);
  }

  #[Test]
  public function failover_url() {
    $c= new Connection(Failover::using(['stomp://localhost:61001', 'stomp://localhost:61002'])->byRandom());
    // $c->connect();
  }

  #[Test, Values([['stomp://localhost', Socket::class], ['stomp+ssl://localhost', SSLSocket::class]])]
  public function using_socket($connection, $type) {
    $this->assertInstanceOf($type, Connection::socketFor(new URL($connection)));
  }
}