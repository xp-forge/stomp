<?php namespace peer\stomp\unittest;

use lang\IllegalStateException;
use peer\stomp\{AckMode, Destination, Message, ReceivedMessage, SendableMessage, Subscription, Transaction};
use test\{Assert, Expect, Test};

class MessageTest {

  /** Helper */
  private function subscriptionWithAckMode($conn, $ackMode) {
    $s= $conn->subscribeTo(new Subscription('/queue/foobar', null, $ackMode));
    $conn->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    return $conn->receive();
  }

  #[Test]
  public function create() {
    new SendableMessage();
  }

  #[Test]
  public function receive_message() {
    $conn= new TestingConnection();
    $s= $conn->subscribeTo(new Subscription('/queue/foobar'));
    $conn->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $conn->receive();
    Assert::instance(Message::class, $m);
  }

  #[Test]
  public function receive_message_with_subscription() {
    $conn= new TestingConnection();
    $s= $conn->subscribeTo(new Subscription('/queue/foobar'));
    $conn->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $conn->receive();
    Assert::equals($s, $m->getSubscription());
  }

  #[Test]
  public function receive_message_has_destination() {
    $conn= new TestingConnection();
    $s= $conn->subscribeTo(new Subscription('/queue/foobar'));
    $conn->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $conn->receive();
    Assert::instance(Destination::class, $m->getDestination());
  }

  #[Test]
  public function ack() {
    $conn= new TestingConnection();
    $s= $conn->subscribeTo(new Subscription('/queue/foobar'));
    $conn->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $conn->receive();
    $conn->clearSentBytes();
    $m->ack();

    Assert::equals("ACK\n".
      "message-id:".$m->getMessageId()."\n".
      "subscription:".$m->getSubscription()->getId()."\n".
      "\n\0",
      $conn->readSentBytes()
    );
  }

  #[Test]
  public function nack() {
    $conn= new TestingConnection();
    $s= $conn->subscribeTo(new Subscription('/queue/foobar'));
    $conn->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $conn->receive();
    $conn->clearSentBytes();
    $m->nack();

    Assert::equals("NACK\n".
      "message-id:".$m->getMessageId()."\n".
      "subscription:".$m->getSubscription()->getId()."\n".
      "\n\0",
      $conn->readSentBytes()
    );
  }

  #[Test]
  public function ack_in_transaction() {
    $conn= new TestingConnection();
    $s= $conn->subscribeTo(new Subscription('/queue/foobar'));
    $t= $conn->begin(new Transaction());
    $conn->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $conn->receive();
    $conn->clearSentBytes();
    $m->ack($t);

    Assert::equals("ACK\n".
      "message-id:".$m->getMessageId()."\n".
      "subscription:".$m->getSubscription()->getId()."\n".
      "transaction:".$t->getName()."\n".
      "\n\0",
      $conn->readSentBytes()
    );
  }

  #[Test]
  public function nack_in_transaction() {
    $conn= new TestingConnection();
    $s= $conn->subscribeTo(new Subscription('/queue/foobar'));
    $t= $conn->begin(new Transaction());
    $conn->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $conn->receive();
    $conn->clearSentBytes();
    $m->nack($t);

    Assert::equals("NACK\n".
      "message-id:".$m->getMessageId()."\n".
      "subscription:".$m->getSubscription()->getId()."\n".
      "transaction:".$t->getName()."\n".
      "\n\0",
      $conn->readSentBytes()
    );
  }

  #[Test, Expect(class: IllegalStateException::class, message: '/Cannot ack message without connection/')]
  public function ack_fails_without_connection() {
    $conn= new TestingConnection();
    $m= new ReceivedMessage();
    $m->ack();
  }

  #[Test, Expect(class: IllegalStateException::class, message: '/Cannot ack message without connection/')]
  public function nack_fails_without_connection() {
    $conn= new TestingConnection();
    $m= new ReceivedMessage();
    $m->nack();
  }

  #[Test]
  public function send() {
    $conn= new TestingConnection();
    $m= new SendableMessage('Hello World.', 'text/plain');

    $conn->getDestination('/queue/foobar')->send($m);
    Assert::equals("SEND\n".
      "content-length:12\n".
      "content-type:text/plain\n".
      "persistent:true\n".
      "destination:/queue/foobar\n".
      "\n".
      "Hello World.".
      "\0",
      $conn->readSentBytes()
    );
  }

  #[Test]
  public function send_with_content_length() {
    $conn= new TestingConnection();
    $m= new SendableMessage('Hello World.', 'text/plain');

    $conn->getDestination('/queue/foobar')->send($m);
    Assert::equals("SEND\n".
      "content-length:12\n".
      "content-type:text/plain\n".
      "persistent:true\n".
      "destination:/queue/foobar\n".
      "\n".
      "Hello World.".
      "\0",
      $conn->readSentBytes()
    );
  }

  #[Test]
  public function receive_and_resend() {
    $conn= new TestingConnection();
    $s= $conn->subscribeTo(new Subscription('/queue/foobar'));
    $conn->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "persistent:true\n".
      "x-xp-customheader:6100\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $conn->receive()->toSendable();
    $conn->clearSentBytes();

    $conn->getDestination('/queue/another')->send($m);
    Assert::equals("SEND\n".
      "message-id:12345\n".
      "content-length:12\n".
      "persistent:true\n".
      "x-xp-customheader:6100\n".
      "destination:/queue/another\n".
      "\n".
      "Hello World!\0",
      $conn->readSentBytes()
    );
  }


  #[Test]
  public function receive_and_resend_nonpersistence() {
    $conn= new TestingConnection();
    $s= $conn->subscribeTo(new Subscription('/queue/foobar'));
    $conn->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "content-type:application/text; charset=utf-8\n".
      "message-id:12345\n".
      "subscription:".$s->getId()."\n".
      "x-xp-customheader:6100\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $conn->receive()->toSendable();
    $conn->clearSentBytes();

    $conn->getDestination('/queue/another')->send($m);
    Assert::equals("SEND\n".
      "message-id:12345\n".
      "content-length:12\n".
      "content-type:application/text; charset=utf-8\n".
      "x-xp-customheader:6100\n".
      "destination:/queue/another\n".
      "\n".
      "Hello World!\0",
      $conn->readSentBytes()
    );
  }

  #[Test]
  public function not_ackable_with_auto_subscription() {
    $conn= new TestingConnection();
    Assert::equals(false, $this->subscriptionWithAckMode($conn, AckMode::AUTO)->ackable());
  }

  #[Test]
  public function ackable_with_client_subscription() {
    $conn= new TestingConnection();
    Assert::equals(true, $this->subscriptionWithAckMode($conn, AckMode::CLIENT)->ackable());
  }

  #[Test]
  public function ackable_with_clientindividual_subscription() {
    $conn= new TestingConnection();
    Assert::equals(true, $this->subscriptionWithAckMode($conn, AckMode::INDIVIDUAL)->ackable());
  }

  #[Test]
  public function headers_initially_empty() {
    $conn= new TestingConnection();
    $m= new SendableMessage('body', 'text/plain');
    Assert::equals([], $m->getHeaders());
  }

  #[Test]
  public function headers() {
    $conn= new TestingConnection();
    $m= new SendableMessage('body', 'text/plain');
    $m->addHeader('x-test', 'test');
    Assert::equals(['x-test' => 'test'], $m->getHeaders());
  }

  #[Test]
  public function header() {
    $conn= new TestingConnection();
    $m= new SendableMessage('body', 'text/plain');
    $m->addHeader('x-test', 'test');
    Assert::equals('test', $m->getHeader('x-test'));
  }

  #[Test]
  public function non_existant_header() {
    $conn= new TestingConnection();
    $m= new SendableMessage('body', 'text/plain');
    Assert::null($m->getHeader('non-existant'));
  }
}