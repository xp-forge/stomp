<?php namespace org\codehaus\stomp\unittest;

use peer\stomp\Message;
use peer\stomp\SendableMessage;
use peer\stomp\ReceivedMessage;
use peer\stomp\Subscription;
use peer\stomp\Transaction;

class MessageTest extends BaseTest {
  
  /**
   * Test
   *
   */
  #[@test]
  public function create() {
    new SendableMessage();
  }

  /**
   * Test
   *
   */
  #[@test]
  public function receive_message() {
    $s= $this->fixture->subscribeTo(new Subscription('/queue/foobar'));
    $this->fixture->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $this->fixture->receive();
    $this->assertInstanceOf('peer.stomp.Message', $m);
  }

  /**
   * Test
   *
   */
  #[@test]
  public function receive_message_with_subscription() {
    $s= $this->fixture->subscribeTo(new Subscription('/queue/foobar'));
    $this->fixture->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $this->fixture->receive();
    $this->assertEquals($s, $m->getSubscription());
  }


  /**
   * Test
   *
   */
  #[@test]
  public function receive_message_has_destination() {
    $s= $this->fixture->subscribeTo(new Subscription('/queue/foobar'));
    $this->fixture->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $this->fixture->receive();
    $this->assertInstanceOf('peer.stomp.Destination', $m->getDestination());
  }

  /**
   * Test
   *
   */
  #[@test]
  public function ack() {
    $s= $this->fixture->subscribeTo(new Subscription('/queue/foobar'));
    $this->fixture->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $this->fixture->receive();
    $this->fixture->clearSentBytes();
    $m->ack();

    $this->assertEquals("ACK\n".
      "message-id:".$m->getMessageId()."\n".
      "subscription:".$m->getSubscription()->getId()."\n".
      "\n\0",
      $this->fixture->readSentBytes()
    );
  }

  /**
   * Test
   *
   */
  #[@test]
  public function nack() {
    $s= $this->fixture->subscribeTo(new Subscription('/queue/foobar'));
    $this->fixture->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $this->fixture->receive();
    $this->fixture->clearSentBytes();
    $m->nack();

    $this->assertEquals("NACK\n".
      "message-id:".$m->getMessageId()."\n".
      "subscription:".$m->getSubscription()->getId()."\n".
      "\n\0",
      $this->fixture->readSentBytes()
    );
  }

  /**
   * Test
   *
   */
  #[@test]
  public function ack_in_transaction() {
    $s= $this->fixture->subscribeTo(new Subscription('/queue/foobar'));
    $t= $this->fixture->begin(new Transaction());
    $this->fixture->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $this->fixture->receive();
    $this->fixture->clearSentBytes();
    $m->ack($t);

    $this->assertEquals("ACK\n".
      "message-id:".$m->getMessageId()."\n".
      "subscription:".$m->getSubscription()->getId()."\n".
      "transaction:".$t->getName()."\n".
      "\n\0",
      $this->fixture->readSentBytes()
    );
  }

  /**
   * Test
   *
   */
  #[@test]
  public function nack_in_transaction() {
    $s= $this->fixture->subscribeTo(new Subscription('/queue/foobar'));
    $t= $this->fixture->begin(new Transaction());
    $this->fixture->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $this->fixture->receive();
    $this->fixture->clearSentBytes();
    $m->nack($t);

    $this->assertEquals("NACK\n".
      "message-id:".$m->getMessageId()."\n".
      "subscription:".$m->getSubscription()->getId()."\n".
      "transaction:".$t->getName()."\n".
      "\n\0",
      $this->fixture->readSentBytes()
    );
  }

  /**
   * Test
   *
   */
  #[@test, @expect(class= 'lang.IllegalStateException', withMessage= '/Cannot ack message without connection/')]
  public function ack_fails_without_connection() {
    $m= new ReceivedMessage();
    $m->ack();
  }

  /**
   * Test
   *
   */
  #[@test, @expect(class= 'lang.IllegalStateException', withMessage= '/Cannot ack message without connection/')]
  public function nack_fails_without_connection() {
    $m= new ReceivedMessage();
    $m->nack();
  }

  /**
   * Test
   *
   */
  #[@test]
  public function send() {
    $m= new SendableMessage('Hello World.', 'text/plain');

    $this->fixture->getDestination('/queue/foobar')->send($m);
    $this->assertEquals("SEND\n".
      "content-length:12\n".
      "content-type:text/plain\n".
      "persistence:true\n".
      "destination:/queue/foobar\n".
      "\n".
      "Hello World.".
      "\0",
      $this->fixture->readSentBytes()
    );
  }

  /**
   * Test
   *
   */
  #[@test]
  public function send_with_content_length() {
    $m= new SendableMessage('Hello World.', 'text/plain');

    $this->fixture->getDestination('/queue/foobar')->send($m);
    $this->assertEquals("SEND\n".
      "content-length:12\n".
      "content-type:text/plain\n".
      "persistence:true\n".
      "destination:/queue/foobar\n".
      "\n".
      "Hello World.".
      "\0",
      $this->fixture->readSentBytes()
    );
  }

  /**
   * Test
   *
   */
  #[@test]
  public function receive_and_resend() {
    $s= $this->fixture->subscribeTo(new Subscription('/queue/foobar'));
    $this->fixture->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "persistence:true\n".
      "x-xp-customheader:6100\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $this->fixture->receive()->toSendable();
    $this->fixture->clearSentBytes();

    $this->fixture->getDestination('/queue/another')->send($m);
    $this->assertEquals("SEND\n".
      "message-id:12345\n".
      "content-length:12\n".
      "persistence:true\n".
      "x-xp-customheader:6100\n".
      "destination:/queue/another\n".
      "\n".
      "Hello World!\0",
      $this->fixture->readSentBytes()
    );
  }


  /**
   * Test
   *
   */
  #[@test]
  public function receive_and_resend_nonpersistence() {
    $s= $this->fixture->subscribeTo(new Subscription('/queue/foobar'));
    $this->fixture->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "content-type:application/text; charset=utf-8\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "x-xp-customheader:6100\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $this->fixture->receive()->toSendable();
    $this->fixture->clearSentBytes();

    $this->fixture->getDestination('/queue/another')->send($m);
    $this->assertEquals("SEND\n".
      "message-id:12345\n".
      "content-length:12\n".
      "content-type:application/text; charset=utf-8\n".
      "x-xp-customheader:6100\n".
      "destination:/queue/another\n".
      "\n".
      "Hello World!\0",
      $this->fixture->readSentBytes()
    );
  }

  private function subscriptionWithAckMode($ackMode) {
    $s= $this->fixture->subscribeTo(new Subscription('/queue/foobar', NULL, $ackMode));
    $this->fixture->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    return $this->fixture->receive();
  }

  /**
   * Test
   *
   */
  #[@test]
  public function not_ackable_with_auto_subscription() {
    $this->assertEquals(FALSE, $this->subscriptionWithAckMode(\peer\stomp\AckMode::AUTO)->ackable());
  }

  /**
   * Test
   *
   */
  #[@test]
  public function ackable_with_client_subscription() {
    $this->assertEquals(TRUE, $this->subscriptionWithAckMode(\peer\stomp\AckMode::CLIENT)->ackable());
  }

  /**
   * Test
   *
   */
  #[@test]
  public function ackable_with_clientindividual_subscription() {
    $this->assertEquals(TRUE, $this->subscriptionWithAckMode(\peer\stomp\AckMode::INDIVIDUAL)->ackable());
  }
}
