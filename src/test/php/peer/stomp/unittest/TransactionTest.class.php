<?php namespace peer\stomp\unittest;

use lang\IllegalStateException;
use peer\stomp\Transaction;
use unittest\{Expect, Test};

class TransactionTest extends BaseTest {

  #[Test]
  public function create() {
    $t= new Transaction();
    $this->assertTrue(0 < strlen($t->getName()));
  }

  #[Test]
  public function accepts_transaction_name() {
    $t= new Transaction('foobar');
    $this->assertEquals('foobar', $t->getName());
  }

  #[Test]
  public function begin_returns_transaction() {
    $tOrig= new Transaction();
    $tNew= $this->fixture->begin($tOrig);

    $this->assertEquals($tOrig, $tNew);
  }

  #[Test]
  public function begin() {
    $transaction= $this->fixture->begin(new Transaction('mytransaction'));

    $this->assertEquals("BEGIN\n".
      "transaction:mytransaction\n".
      "\n\0",
      $this->fixture->readSentBytes()
    );
  }

  #[Test]
  public function begin_then_rollback() {
    $transaction= $this->fixture->begin(new Transaction('mytransaction'));
    $transaction->rollback();

    $this->assertEquals("BEGIN\n".
      "transaction:mytransaction\n".
      "\n\0".
      "ABORT\n".
      "transaction:mytransaction\n".
      "\n\0",
      $this->fixture->readSentBytes()
    );
  }

  #[Test, Expect(IllegalStateException::class)]
  public function rollback_fails_when_not_begun() {
    (new Transaction())->rollback();
  }

  #[Test]
  public function begin_then_commit() {
    $transaction= $this->fixture->begin(new Transaction('mytransaction'));
    $transaction->commit();

    $this->assertEquals("BEGIN\n".
      "transaction:mytransaction\n".
      "\n\0".
      "COMMIT\n".
      "transaction:mytransaction\n".
      "\n\0",
      $this->fixture->readSentBytes()
    );
  }

  #[Test, Expect(IllegalStateException::class)]
  public function commit_fails_when_not_begun() {
    (new Transaction())->commit();
  }

  #[Test, Expect(IllegalStateException::class)]
  public function commit_fails_on_second_call() {
    try {
      $transaction= $this->fixture->begin(new Transaction('mytransaction'));
      $transaction->commit();
    } catch (IllegalStateException $e) {
      $this->fail('Expected exception occurred too early.', null, null);
    }

    // This should create the expected exception
    $transaction->commit();
  }
}