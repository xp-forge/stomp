<?php namespace peer\stomp;

use peer\stomp\frame\SubscribeFrame;
use peer\stomp\frame\UnsubscribeFrame;

/**
 * Subscription
 * 
 */
class Subscription extends \lang\Object {
  protected $id         = NULL;
  protected $dest       = NULL;
  protected $destination= NULL;
  protected $ackMode    = NULL;
  protected $selector   = NULL;
  protected $callback   = NULL;

  /**
   * Constructor
   *
   * @param   string destination
   * @param   callable $callback callback method upon message arrival
   * @param   string ackMode default AckMode::INDIVIDUAL
   * @param   string selector default NULL
   */
  public function __construct($destination, $callback= NULL, $ackMode= AckMode::INDIVIDUAL, $selector= NULL) {
    $this->dest= $destination;
    $this->withCallback($callback);
    $this->setAckMode($ackMode);
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

  /**
   * Set subscription id 
   * 
   * @param string $id 
   */
  public function setId($id) {
    $this->id= $id;
  }

  /**
   * Retrieve ack mode
   * 
   * @return int
   */
  public function getAckMode() {
    return $this->ackMode;
  }

  /**
   * Set ack mode - see peer.stomp.AckMode
   * 
   * @param int $ackMode
   */
  public function setAckMode($ackMode) {
    if (!in_array($ackMode, array(AckMode::AUTO, AckMode::CLIENT, AckMode::INDIVIDUAL))) {
      throw new \lang\IllegalArgumentException('Invalid ackMode given: "'.$ackMode.'"');
    }
    $this->ackMode= $ackMode;
  }

  /**
   * Set callback function
   * 
   * @param  callable $callback
   */
  public function withCallback($callback) {
    $this->callback= $callback;
  }

  /**
   * Create a subscription on a destination
   *
   * @param  peer.stomp.Connection $conn
   * @throws lang.Throwable If any error occurrs
   */
  public function subscribe(Connection $conn) {
    $this->destination= $conn->getDestination($this->dest);

    try {
      $this->id= uniqid('xp-sub-');

      $frame= new SubscribeFrame($this->destination->getName(), $this->ackMode, $this->selector);
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

    $this->destination->getConnection()->sendFrame(new UnsubscribeFrame(NULL, $this->id));
    $this->destination->getConnection()->_unsubscribe($this);

    $this->destination= NULL;
    $this->id= NULL;
  }

  /**
   * Process a message for this subscription
   * 
   * @param  peer.stomp.ReceivedMessage $message
   */
  public function process(ReceivedMessage $message) {
    call_user_func_array($this->callback, array($message));
  }

  /**
   * Retrieve string representation
   * 
   * @return string
   */
  public function toString() {
    return sprintf('%s (dest= %s, ackmode= %s, selector= %s)',
      $this->getClassName(),
      $this->dest,
      $this->ackMode,
      \xp::stringOf($this->selector)
    );
  }
}
