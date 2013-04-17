<?php
/*
 * This class is part of the XP Framework
 *
 */

  uses(
    'unittest.TestCase',
    'org.codehaus.stomp.StompSubscription'
  );

  class StompSubscriptionTest extends TestCase {

    /**
     * Test
     *
     */
    #[@test]
    public function create() {
      new StompSubscription('/queue/foo');
    }
  }
?>