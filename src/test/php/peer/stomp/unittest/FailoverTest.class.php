<?php namespace peer\stomp\unittest;

use peer\stomp\Failover;
  use test\{Assert, Expect, Test};

class FailoverTest {

  #[Test]
  public function create() {
    new Failover(['foo']);
  }

  #[Test]
  public function create_using() {
    Assert::instance(Failover::class, Failover::using(['foo']));
  }

  #[Test]
  public function create_change_election() {
    Assert::instance(Failover::class, Failover::using(['Foo'])->byRandom());
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

    Assert::equals(1, sizeof($seen));
  }

  #[Test]
  public function elect_random_visits_all() {
    $f= Failover::using([1, 2, 3, 4, 5])->byRandom();

    $seen= [];
    $f->elect(function($member) use (&$seen) {
      $seen[]= $member;
      return false;
    });

    Assert::equals(5, sizeof($seen));
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

    Assert::equals($items, $seen);
  }

  #[Test]
  public function elect_returns_elected_member() {
    $f= Failover::using([1, 2, 3, 4, 5])->bySerial();
    Assert::equals(1, $f->elect(function($member) { return true; }));
  }

  #[Test]
  public function elect_returns_null_when_no_member_elected() {
    $f= Failover::using([1, 2, 3, 4, 5])->bySerial();
    Assert::equals(null, $f->elect(function($member) { return false; }));
  }
}