<?php namespace org\codehaus\stomp\unittest;

use \org\codehaus\stomp\Message;
use \org\codehaus\stomp\Subscription;

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
    var_dump($m);
  }
}
