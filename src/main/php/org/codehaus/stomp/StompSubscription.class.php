<?php
/*
 * This class is part of the XP Framework
 *
 */

  uses(
    'org.codehaus.stomp.frame.SubscribeFrame'
  );

  class StompSubscription extends Object {
    protected $id         = NULL;
    protected $destination= NULL;
    protected $ackMode    = NULL;
    protected $selector   = NULL;

    protected $conn       = NULL;

    /**
     * Constructor
     *
     * @param   string destination
     * @param   string ackMode default AckMode::INDIVIDUAL
     * @param   string selector default NULL
     * @throws  lang.IllegalArgumentExcpetion
     */
    public function __construct($destination, $ackMode= AckMode::INDIVIDUAL, $selector= NULL) {
      if (empty($destination)) {
        throw new IllegalArgumentException('Invalid destination given: "'.$destination.'"');
      }

      $this->destination= $destination;

      if (!in_array($ackMode, array(AckMode::AUTO, AckMode::CLIENT, AckMode::INDIVIDUAL))) {
        throw new IllegalArgumentException('Invalid ackMode given: "'.$ackMode.'"');
      }

      $this->ackMode= $ackMode;
      $this->selector= $selector;
    }

    public function getId() {
      return $this->id;
    }

    public function send(StompConnection $conn) {
      try {
        $this->id= uniqid(__CLASS__.'.');

        $frame= new org·codehaus·stomp·frame·SubscribeFrame($this->destination, $this->ackMode, $this->selector);
        $frame->setId($this->id);

        $this->conn= $conn;

        $this->conn->sendFrame($frame);
      } catch (Throwable $t) {
        $this->id= NULL;
        throw $t;
      }
    }
  }
?>