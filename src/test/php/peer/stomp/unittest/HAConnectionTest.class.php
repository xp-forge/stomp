<?php namespace peer\stomp\unittest;

use lang\IllegalArgumentException;
use peer\ConnectException;
use peer\URL;
use peer\stomp\Connection;
use peer\stomp\HAConnection;
use unittest\TestCase;

class HAConnectionTest extends TestCase {

  /** @return  var[] */
  protected function constructorArgs() {
    return ['stomp://localhost:61003', new URL('stomp://localhost:61003')];
  }

  #[@test, @values('constructorArgs')]
  public function can_create($arg) {
    new HAConnection($arg);
  }

  #[@test, @values('constructorArgs')]
  public function url_accessor_returns_url($arg) {
    $this->assertEquals(new URL('stomp://localhost:61003'), (new HAConnection($arg))->url());
  }

  #[@test, @values([null, 'localhost:61003']), @expect(IllegalArgumentException::class)]
  public function invalid_url_given_to_constructor($arg) {
    new HAConnection($arg);
  }

  #[@test]
  public function iconn_as_constructor_arg() {
    new HAConnection(new Connection('stomp://localhost:61003/'));
  }

  #[@test, @expect(IllegalArgumentException::class)]
  public function create_endpoint_with_none_is_failure() {
    new HAConnection([]);
  }

  #[@test]
  public function create_endpoint_with_one() {
    $this->assertEquals(1, (new HAConnection(['stomp://localhost:61001']))->poolSize());
  }

  #[@test]
  public function create_endpoint_with_many() {
    new HAConnection(['stomp://localhost:61001', 'stomp://localhost:61002', new URL('stomp://localhost:61003')]);
  }

  #[@test]
  public function elect_the_successful_one() {
    $ha= new HAConnection([
      new MockConnection('one', false),
      new MockConnection('two', true)
    ]);

    $ha->connect();
    $this->assertEquals('two', $ha->conn()->name);
  }

  #[@test]
  public function elect_the_successful_one_2() {
    $ha= new HAConnection([
      new MockConnection('one', true),
      new MockConnection('two', false)
    ]);

    $ha->connect();
    $this->assertEquals('one', $ha->conn()->name);
  }

  #[@test, @expect(ConnectException::class)]
  public function election_fails_with_no_successful() {
    $ha= new HAConnection([
      new MockConnection('one', false),
      new MockConnection('two', false)
    ]);

    $ha->connect();
    $this->assertEquals('one', $ha->conn()->name);
  }

  #[@test]
  public function elect_the_one() {
    $ha= new HAConnection([
      new MockConnection('one', true),
    ]);

    $ha->connect();
    $this->assertEquals('one', $ha->conn()->name);
  }

  #[@test]
  public function elect_the_one_even_when_unsuccessful() {
    $ha= new HAConnection([
      new MockConnection('one', false),
    ]);

    try {
      $ha->connect();
    } catch (ConnectException $e) {
      // Intentionally ignored
    }

    $this->assertEquals('one', $ha->conn()->name);
  }
}