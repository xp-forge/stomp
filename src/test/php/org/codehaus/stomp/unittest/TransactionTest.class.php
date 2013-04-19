<?php namespace org\codehaus\stomp\unittest;

  use \org\codehaus\stomp\Transaction;

  class TransactionTest extends BaseTest {

    /**
     * Test
     *
     */
    #[@test]
    public function create() {
      $t= new Transaction();
      $this->assertTrue(0 < strlen($t->getName()));
    }

    /**
     * Test
     *
     */
    #[@test]
    public function accepts_transaction_name() {
      $t= new Transaction('foobar');
      $this->assertEquals('foobar', $t->getName());
    }

    /**
     * Test
     *
     */
    #[@test]
    public function begin_returns_transaction() {
      $tOrig= new Transaction();
      $tNew= $this->fixture->begin($tOrig);

      $this->assertEquals($tOrig, $tNew);
    }

    /**
     * Test
     *
     */
    #[@test]
    public function begin() {
      $transaction= $this->fixture->begin(new Transaction('mytransaction'));

      $this->assertEquals("BEGIN\n".
        "transaction:mytransaction\n".
        "\n\0",
        $this->fixture->readSentBytes()
      );
    }

    /**
     * Test
     *
     */
    #[@test]
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

    /**
     * Test
     *
     */
    #[@test, @expect('lang.IllegalStateException')]
    public function rollback_fails_when_not_begun() {
      create(new Transaction())->rollback();
    }

    /**
     * Test
     *
     */
    #[@test]
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

    /**
     * Test
     *
     */
    #[@test, @expect('lang.IllegalStateException')]
    public function commit_fails_when_not_begun() {
      create(new Transaction())->commit();
    }

    /**
     * Test
     *
     */
    #[@test, @expect('lang.IllegalStateException')]
    public function commit_fails_on_second_call() {
      try {
        $transaction= $this->fixture->begin(new Transaction('mytransaction'));
        $transaction->commit();
      } catch (\lang\IllegalStateException $e) {
        $this->fail('Expected exception occurred too early.', NULL, NULL);
      }

      // This should create the expected exception
      $transaction->commit();
    }
  }
?>