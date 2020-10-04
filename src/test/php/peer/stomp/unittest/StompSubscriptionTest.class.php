<?php namespace peer\stomp\unittest;

use lang\{IllegalArgumentException, IllegalStateException};
use peer\stomp\{Exception, Subscription};
use unittest\{Expect, Test};

class StompSubscriptionTest extends BaseTest {

  #[Test]
  public function create() {
    new Subscription($this->fixture->getDestination('/queue/foo'));
  }

  #[Test]
  public function subscribe() {
    $subscription= $this->fixture->subscribeTo(new Subscription('/queue/foo'));

    $this->assertEquals("SUBSCRIBE\n".
      "destination:/queue/foo\n".
      "ack:client-individual\n".
      "id:".$subscription->getId()."\n".
      "\n\0",
      $this->fixture->readSentBytes()
    );
  }

  #[Test]
  public function subscription_registered_in_connection() {
    $subscription= $this->fixture->subscribeTo(new Subscription('/queue/foo'));

    $this->assertEquals($subscription, $this->fixture->subscriptionById($subscription->getId()));
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
    $subscription= $this->fixture->subscribeTo(new Subscription('/queue/foo'));
    $id= $subscription->getId();

    $subscription->unsubscribe();

    $this->assertEquals("SUBSCRIBE\n".
      "destination:/queue/foo\n".
      "ack:client-individual\n".
      "id:".$id."\n".
      "\n\0".
      "UNSUBSCRIBE\n".
      "id:".$id."\n".
      "\n\0",
      $this->fixture->readSentBytes()
    );
  }

  protected function createSubscription() {
    return $this->fixture->subscribeTo(new Subscription('/queue/foo'))->getId();
  }

  #[Test]
  public function subscribe_registeres_in_connection() {
    $id= $this->createSubscription();
    $this->assertInstanceOf(Subscription::class, $this->fixture->subscriptionById($id));
  }

  #[Test, Expect(Exception::class)]
  public function subscribe_also_unregisteres_in_connection() {
    $id= $this->createSubscription();
    $this->fixture->subscriptionById($id)->unsubscribe();

    $this->fixture->subscriptionById($id);
  }

  #[Test]
  public function ackmode() {
    $s= new Subscription('foobar');
    $s->setAckMode(\peer\stomp\AckMode::AUTO);
    $s->setAckMode(\peer\stomp\AckMode::CLIENT);
    $s->setAckMode(\peer\stomp\AckMode::INDIVIDUAL);
  }

  #[Test, Expect(IllegalArgumentException::class)]
  public function invalid_ackmode() {
    $s= new Subscription('foobar');
    $s->setAckMode('automatic');
  }

  #[Test]
  public function subscribe_with_callback() {
    $called= false;
    $sub= $this->fixture->subscribeTo(new Subscription('/queue/foobar', function($message) use(&$called) {
      $called= true;
    }));
    $this->fixture->setResponseBytes("MESSAGE\n".
      "message-id:12345\n".
      "subscription:".$sub->getId()."\n".
      "destination:/queue/foobar\n".
      "\n".
      "Hello World.\0"
    );

    $this->fixture->consume(1);
    $this->assertEquals(true, $called);
  }
}