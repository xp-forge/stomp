<?php namespace peer\stomp\unittest;

use lang\IllegalArgumentException;
use peer\stomp\{Connection, Failover};
use peer\{CryptoSocket, Socket, URL};
use test\{Assert, Expect, Test, Values};

class ConnectionTest {

  /** @return iterable */
  private function uris() {
    yield ['stomp://localhost:61003'];
    yield [new URL('stomp://localhost:61003')];
  }

  /** @return iterable */
  private function crypto() {
    yield ['stomp+ssl://localhost', STREAM_CRYPTO_METHOD_ANY_CLIENT];
    yield ['stomp+tls://localhost', STREAM_CRYPTO_METHOD_TLS_CLIENT];
    yield ['stomp+sslv2://localhost', STREAM_CRYPTO_METHOD_SSLv2_CLIENT];
    yield ['stomp+sslv23://localhost', STREAM_CRYPTO_METHOD_SSLv23_CLIENT];
    yield ['stomp+tlsv12://localhost', STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT];
  }

  #[Test, Values(from: 'uris')]
  public function can_create($arg) {
    new Connection($arg);
  }

  #[Test, Values(from: 'uris')]
  public function url_accessor_returns_url($arg) {
    Assert::equals(new URL('stomp://localhost:61003'), (new Connection($arg))->url());
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

  #[Test]
  public function uses_socket() {
    $socket= Connection::socketFor(new URL('stomp://localhost'));

    Assert::instance(Socket::class, $socket);
  }

  #[Test, Values(from: 'crypto')]
  public function uses_crypto_socket($connection, $impl) {
    $socket= Connection::socketFor(new URL($connection));

    Assert::instance(CryptoSocket::class, $socket);
    Assert::equals($impl, $socket->cryptoImpl);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function invalid_crypto_implementation() {
    Connection::socketFor(new URL('stomp+tlsv999://localhost'));
  }
}