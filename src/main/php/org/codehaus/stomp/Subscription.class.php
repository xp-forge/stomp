<?php namespace org\codehaus\stomp;

class Subscription extends \lang\Object {
  protected $id         = NULL;
  protected $dest       = NULL;
  protected $destination= NULL;
  protected $ackMode    = NULL;
  protected $selector   = NULL;

  /**
   * Constructor
   *
   * @param   string destination
   * @param   string ackMode default AckMode::INDIVIDUAL
   * @param   string selector default NULL
   * @throws  lang.IllegalArgumentException
   */
  public function __construct($destination, $ackMode= AckMode::INDIVIDUAL, $selector= NULL) {
    if (!in_array($ackMode, array(AckMode::AUTO, AckMode::CLIENT, AckMode::INDIVIDUAL))) {
      throw new \lang\IllegalArgumentException('Invalid ackMode given: "'.$ackMode.'"');
    }

    $this->dest= $destination;
    $this->ackMode= $ackMode;
    $this->selector= $selector;
  }

  /**
   * Retrieve subscription id
   *
   * @return string
   */
  public function getId() {
    return $this->id;
  }

  public function setId($id) {
    $this->id= $id;
  }

  /**
   * Create a subscription on a destination
   *
   * @param  org.codehaus.stomp.StompConnection $conn
   * @throws lang.Throwable If any error occurrs
   */
  public function subscribe(StompConnection $conn) {
    $this->destination= $conn->getDestination($this->dest);

    try {
      $this->id= uniqid('xp.stomp.subscription.');

      $frame= new frame\SubscribeFrame($this->destination->getName(), $this->ackMode, $this->selector);
      $frame->setId($this->id);

      $this->destination->getConnection()->sendFrame($frame);
    } catch (\lang\Throwable $t) {
      $this->id= NULL;
      throw $t;
    }
  }

  /**
   * Unsubscribe
   *
   * @throws  lang.IllegalStateException when not subscribed
   */
  public function unsubscribe() {
    if (!$this->id) {
      throw new \lang\IllegalStateException('Cannot unsubscribe when not subscribed.');
    }

    if (!$this->destination instanceof Destination) {
      throw new \lang\IllegalStateException('Cannot unsubscribe when not subscribed.');
    }

    $this->destination->getConnection()->sendFrame(new frame\UnsubscribeFrame(NULL, $this->id));
    $this->destination->getConnection()->unsubscribe($this);

    $this->destination= NULL;
    $this->id= NULL;
  }
}
