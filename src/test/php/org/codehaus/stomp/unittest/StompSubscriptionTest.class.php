<?php namespace org\codehaus\stomp\unittest;

use \org\codehaus\stomp\Subscription;

class StompSubscriptionTest extends BaseTest {

  /**
   * Test
   *
   */
  #[@test]
  public function create() {
    new Subscription($this->fixture->getDestination('/queue/foo'));
  }

  /**
   * Test
   *
   */
  #[@test]
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

  /**
   * Test
   *
   */
  #[@test]
  public function subscription_registered_in_connection() {
    $subscription= $this->fixture->subscribeTo(new Subscription('/queue/foo'));

    $this->assertEquals($subscription, $this->fixture->subscriptionById($subscription->getId()));
  }

  /**
   * Test
   *
   */
  #[@test, @expect('lang.IllegalStateException')]
  public function unsubscribe_not_possible_when_not_subscribed() {
    create(new Subscription('foo'))->unsubscribe();
  }

  /**
   * Test
   *
   */
  #[@test, @expect('lang.IllegalStateException')]
  public function unsubscribe_not_possible_when_no_connection() {
    $s= new Subscription('foo');
    $s->setId('foobar');

    create(new Subscription('foo'))->unsubscribe();
  }

  /**
   * Test
   *
   */
  #[@test]
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

  /**
   * Test
   *
   */
  #[@test, @ignore('cycling reference preventing this')]
  public function destructor_removes_subscription() {
    $id= $this->createSubscription();

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

  /**
   * Test
   *
   */
  #[@test]
  public function subscribe_registeres_in_connection() {
    $id= $this->createSubscription();
    $this->assertInstanceOf('org.codehaus.stomp.Subscription', $this->fixture->subscriptionById($id));
  }

  /**
   * Test
   *
   */
  #[@test, @expect('org.codehaus.stomp.Exception')]
  public function subscribe_also_unregisteres_in_connection() {
    $id= $this->createSubscription();
    $this->fixture->subscriptionById($id)->unsubscribe();

    $this->fixture->subscriptionById($id);
  }
}
