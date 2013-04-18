<?php namespace org\codehaus\stomp\frame;

  /**
   * Abort frame
   *
   */
  class AbortFrame extends Frame {

    /**
     * Constructor
     *
     * @param   string txname
     */
    public function __construct($txname) {
      $this->setTransaction($txname);
    }

    /**
     * Retrieve frame command
     *
     */
    public function command() {
      return 'ABORT';
    }

    /**
     * Set transaction name
     *
     * @param   string name
     */
    public function setTransaction($name) {
      $this->addHeader('transaction', $name);
    }

    /**
     * Retrieve transaction name
     *
     * @return  string
     */
    public function getTransaction() {
      $this->getHeader('transaction');
    }
  }
?>
