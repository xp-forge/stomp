<?php namespace org\codehaus\stomp\unittest;

use \org\codehaus\stomp\Message;

class MessageTest extends BaseTest {
  
  /**
   * Test
   *
   */
  #[@test]
  public function create() {
    new Message();
  }
}
