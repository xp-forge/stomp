<?php namespace peer\stomp;

interface IConnection {
  public function url();
  public function recvFrame($timeout= 0.2);
  public function sendFrame(\peer\stomp\frame\Frame $frame);
  public function connect();
  public function disconnect();
  public function begin(Transaction $t);
  public function subscribeTo(Subscription $s);
  public function subscriptionById($id);
  public function receive($timeout);
  public function consume($timeout);
  public function getDestination($name);
}