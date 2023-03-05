<?php namespace peer\stomp\unittest;

use lang\{IllegalArgumentException, IllegalStateException};
use peer\stomp\{AckMode, Connection, Exception, Subscription};
use test\{Assert, Expect, Test};

class StompSubscriptionTest {

  /** Helper */
  private function createSubscription(Connection $conn) {
    return $conn->subscribeTo(new Subscription('/queue/foo'))->getId();
  }

  #[Test]
  public function create() {
    $conn= new TestingConnection();
    new Subscription($conn->getDestination('/queue/foo'));
  }

  #[Test]
  public function subscribe() {
    $conn= new TestingConnection();
    $subscription= $conn->subscribeTo(new Subscription('/queue/foo'));

    Assert::equals("SUBSCRIBE\n".
      "destination:/queue/foo\n".
      "ack:client-individual\n".
      "id:".$subscription->getId()."\n".
      "\n\0",
      $conn->readSentBytes()
    );
  }

  #[Test]
  public function subscription_registered_in_connection() {
    $conn= new TestingConnection();
    $subscription= $conn->subscribeTo(new Subscription('/queue/foo'));

    Assert::equals($subscription, $conn->subscriptionById($subscription->getId()));
  }

  #[Test, Expect(IllegalStateException::class)]
  public function unsubscribe_not_possible_when_not_subscribed() {
    (new Subscription('foo'))->unsubscribe();
  }

  #[Test, Expect(IllegalStateException::class)]
  public function unsubscribe_not_possible_when_no_connection() {
    $s= new Subscription('foo');
    $s->setId('foobar');

    (new Subscription('foo'))->unsubscribe();
  }

  #[Test]
  public function unsubscribe() {
    $conn= new TestingConnection();
    $subscription= $conn->subscribeTo(new Subscription('/queue/foo'));
    $id= $subscription->getId();

    $subscription->unsubscribe();

    Assert::equals("SUBSCRIBE\n".
      "destination:/queue/foo\n".
      "ack:client-individual\n".
      "id:".$id."\n".
      "\n\0".
      "UNSUBSCRIBE\n".
      "id:".$id."\n".
      "\n\0",
      $conn->readSentBytes()
    );
  }

  #[Test]
  public function subscribe_registeres_in_connection() {
    $conn= new TestingConnection();
    $id= $this->createSubscription($conn);
    Assert::instance(Subscription::class, $conn->subscriptionById($id));
  }

  #[Test, Expect(Exception::class)]
  public function subscribe_also_unregisteres_in_connection() {
    $conn= new TestingConnection();
    $id= $this->createSubscription($conn);
    $conn->subscriptionById($id)->unsubscribe();

    $conn->subscriptionById($id);
  }

  #[Test]
  public function ackmode() {
    $s= new Subscription('foobar');
    $s->setAckMode(AckMode::AUTO);
    $s->setAckMode(AckMode::CLIENT);
    $s->setAckMode(AckMode::INDIVIDUAL);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function invalid_ackmode() {
    $s= new Subscription('foobar');
    $s->setAckMode('automatic');
  }

  #[Test]
  public function subscribe_with_callback() {
    $conn= new TestingConnection();
    $called= 0;
    $sub= $conn->subscribeTo(new Subscription('/queue/foobar', function($message) use(&$called) {
      $called++;
    }));
    $conn->setResponseBytes("MESSAGE\n".
      "message-id:12345\n".
      "subscription:".$sub->getId()."\n".
      "destination:/queue/foobar\n".
      "\n".
      "Hello World.\0"
    );

    $conn->consume(1);
    Assert::equals(1, $called);
  }
}