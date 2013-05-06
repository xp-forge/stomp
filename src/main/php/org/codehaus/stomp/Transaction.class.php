<?php namespace org\codehaus\stomp;

/**
 * Represent a STOMP transaction
 *
 */
class Transaction extends \lang\Object {
  protected $name = NULL;
  protected $conn = NULL;

  /**
   * Constructor
   * 
   * @param string $name optional name of transaction
   */
  public function __construct($name= NULL) {
    if (NULL === $name) $name= uniqid('xp.transaction.');
    $this->name= $name;
  }

  /**
   * Retrieve name of transaction
   *
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Begin new transaction
   *
   * @param  org.codehaus.stomp.Connection $conn
   * @return org.codehaus.stomp.Transaction
   */
  public function begin(Connection $conn) {
    try {
      $this->conn= $conn;
      $conn->sendFrame(new frame\BeginFrame($this->name));
    } catch (\lang\Throwable $t) {
      $this->conn= NULL;
      throw $t;
    }
  }

  /**
   * Rollback
   *
   */
  public function rollback() {
    $this->assertBegun();
    $this->conn->sendFrame(new frame\AbortFrame($this->name));
    $this->conn= NULL;
  }

  /**
   * Commit
   *
   */
  public function commit() {
    $this->assertBegun();
    $this->conn->sendFrame(new frame\CommitFrame($this->name));
    $this->conn= NULL;
  }

  /**
   * Assert a transaction is currently ongoing
   *
   * @throws   If no transaction is running
   */
  protected function assertBegun() {
    if (!$this->conn instanceof Connection) {
      throw new \lang\IllegalStateException('Cannot rollback transaction if not started.');
    }
  }
}
