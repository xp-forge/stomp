<?php namespace peer\stomp\unittest;

use lang\IllegalStateException;
use peer\stomp\Transaction;
use test\{Assert, Expect, Test};

class TransactionTest {

  #[Test]
  public function create() {
    $t= new Transaction();
    Assert::true(0 < strlen($t->getName()));
  }

  #[Test]
  public function accepts_transaction_name() {
    Assert::equals('testing', (new Transaction('testing'))->getName());
  }

  #[Test]
  public function begin_returns_transaction() {
    $conn= new TestingConnection();
    $transaction= new Transaction();

    Assert::equals($transaction, $conn->begin($transaction));
  }

  #[Test]
  public function begin() {
    $conn= new TestingConnection();
    $transaction= $conn->begin(new Transaction('mytransaction'));

    Assert::equals("BEGIN\n".
      "transaction:mytransaction\n".
      "\n\0",
      $conn->readSentBytes()
    );
  }

  #[Test]
  public function begin_then_rollback() {
    $conn= new TestingConnection();
    $transaction= $conn->begin(new Transaction('mytransaction'));
    $transaction->rollback();

    Assert::equals("BEGIN\n".
      "transaction:mytransaction\n".
      "\n\0".
      "ABORT\n".
      "transaction:mytransaction\n".
      "\n\0",
      $conn->readSentBytes()
    );
  }

  #[Test, Expect(IllegalStateException::class)]
  public function rollback_fails_when_not_begun() {
    (new Transaction())->rollback();
  }

  #[Test]
  public function begin_then_commit() {
    $conn= new TestingConnection();
    $transaction= $conn->begin(new Transaction('mytransaction'));
    $transaction->commit();

    Assert::equals("BEGIN\n".
      "transaction:mytransaction\n".
      "\n\0".
      "COMMIT\n".
      "transaction:mytransaction\n".
      "\n\0",
      $conn->readSentBytes()
    );
  }

  #[Test, Expect(IllegalStateException::class)]
  public function commit_fails_when_not_begun() {
    (new Transaction())->commit();
  }

  #[Test, Expect(IllegalStateException::class)]
  public function commit_fails_on_second_call() {
    $conn= new TestingConnection();
    try {
      $transaction= $conn->begin(new Transaction('mytransaction'));
      $transaction->commit();
    } catch (IllegalStateException $e) {
      $this->fail('Expected exception occurred too early.', null, null);
    }

    // This should create the expected exception
    $transaction->commit();
  }
}