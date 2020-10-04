<?php namespace peer\stomp\unittest;

use peer\stomp\Failover;
use unittest\{Expect, Test, TestCase};

class FailoverTest extends TestCase {

  #[Test]
  public function create() {
    new Failover(['foo']);
  }

  #[Test]
  public function create_using() {
    $this->assertInstanceof(Failover::class, Failover::using(['foo']));
  }

  #[Test]
  public function create_change_election() {
    $this->assertInstanceof(Failover::class, Failover::using(['Foo'])->byRandom());
  }

  #[Test, Expect(\lang\IllegalArgumentException::class)]
  public function create_without_members_raises_exception() {
    new Failover([]);
  }

  #[Test]
  public function elect() {
    $f= Failover::using([1, 2, 3, 4, 5])->byRandom();

    $seen= [];
    $f->elect(function($member) use (&$seen) {
      $seen[]= $member;
      return true;
    });

    $this->assertEquals(1, sizeof($seen));
  }

  #[Test]
  public function elect_random_visits_all() {
    $f= Failover::using([1, 2, 3, 4, 5])->byRandom();

    $seen= [];
    $f->elect(function($member) use (&$seen) {
      $seen[]= $member;
      return false;
    });

    $this->assertEquals(5, sizeof($seen));
  }

  #[Test]
  public function elect_serial() {
    $items= [1, 2, 3, 4, 5];
    $f= Failover::using($items)->bySerial();

    $seen= [];
    $f->elect(function($member) use (&$seen) {
      $seen[]= $member;
      return false;
    });

    $this->assertEquals($items, $seen);
  }

  #[Test]
  public function elect_returns_elected_member() {
    $f= Failover::using([1, 2, 3, 4, 5])->bySerial();
    $this->assertEquals(1, $f->elect(function($member) { return true; }));
  }

  #[Test]
  public function elect_returns_null_when_no_member_elected() {
    $f= Failover::using([1, 2, 3, 4, 5])->bySerial();
    $this->assertEquals(null, $f->elect(function($member) { return false; }));
  }
}