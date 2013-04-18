<?php namespace org\codehaus\stomp\frame;

  /**
   * Begin frame
   *
   */
  class BeginFrame extends Frame {

    /**
     * Constructor
     *
     * @param   string txname
     */
    public function __construct($txname) {
      $this->setTransaction($txname);
    }

    /**
     * Frame command
     *
     */
    public function command() {
      return 'BEGIN';
    }

    /**
     * Set transaction
     *
     * @param   string name
     */
    public function setTransaction($name) {
      $this->addHeader('transaction', $name);
    }

    /**
     * Get transaction
     *
     * @return  string
     */
    public function getTransaction() {
      $this->getHeader('transaction');
    }
  }
?>
