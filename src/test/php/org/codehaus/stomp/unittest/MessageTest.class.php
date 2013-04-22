<?php namespace org\codehaus\stomp\unittest;

use \org\codehaus\stomp\Message;
use \org\codehaus\stomp\Subscription;
use \org\codehaus\stomp\Transaction;

class MessageTest extends BaseTest {
  
  /**
   * Test
   *
   */
  #[@test]
  public function create() {
    new Message();
  }

  /**
   * Test
   *
   */
  #[@test]
  public function receive_message() {
    $s= $this->fixture->subscribe(new Subscription('/queue/foo'));
    $this->fixture->setResponseBytes("MESSAGE\n".
      "destination:/queue/foo\n".
      "message-id:12345\n".
      "\n".
      "Hello World!\n".
      "\n\0"
    );

    $m= $this->fixture->receive();
    $this->assertInstanceOf('org.codehaus.stomp.Message', $m);
  }

  /**
   * Test
   *
   */
  #[@test]
  public function receive_message_with_subscription() {
    $s= $this->fixture->subscribe(new Subscription('/queue/foobar'));
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
  public function ack() {
    $s= $this->fixture->subscribe(new Subscription('/queue/foobar'));
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
    $s= $this->fixture->subscribe(new Subscription('/queue/foobar'));
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
    $s= $this->fixture->subscribe(new Subscription('/queue/foobar'));
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
    $s= $this->fixture->subscribe(new Subscription('/queue/foobar'));
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
    $m= new Message();
    $m->ack();
  }

  /**
   * Test
   *
   */
  #[@test, @expect(class= 'lang.IllegalStateException', withMessage= '/Cannot ack message without connection/')]
  public function nack_fails_without_connection() {
    $m= new Message();
    $m->nack();
  }

  /**
   * Test
   *
   */
  #[@test]
  public function send() {
    $m= new Message('Hello World.', 'text/plain');
    $m->setDestination('/queue/foobar');

    $m->send($this->fixture);
    $this->assertEquals("SEND\n".
      "content-type:text/plain\n".
      "destination:/queue/foobar\n".
      "\n".
      "Hello World.".
      "\n\0",
      $this->fixture->readSentBytes()
    );
  }
}
