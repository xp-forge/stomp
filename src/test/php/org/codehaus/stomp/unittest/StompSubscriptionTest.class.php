<?php namespace org\codehaus\stomp\unittest;

  use \org\codehaus\stomp\Subscription;

  class StompSubscriptionTest extends BaseTest {

    /**
     * Test
     *
     */
    #[@test]
    public function create() {
      new Subscription('/queue/foo');
    }

    /**
     * Test
     *
     */
    #[@test]
    public function subscribe() {
      $subscription= $this->fixture->subscribe(new Subscription('/queue/foo'));

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
    #[@test, @expect('lang.IllegalStateException')]
    public function unsubscribe_not_possible_when_not_subscribed() {
      create(new Subscription('foo'))->unsubscribe();
    }

    /**
     * Test
     *
     */
    #[@test]
    public function unsubscribe() {
      $subscription= $this->fixture->subscribe(new Subscription('/queue/foo'));
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
      $s= $this->fixture->subscribe(new Subscription('/queue/foo'));
      return $s->getId();
    }

    /**
     * Test
     *
     */
    #[@test]
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
  }
?>