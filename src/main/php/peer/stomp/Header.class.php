<?php namespace peer\stomp;

/**
 * Interface with shared constants
 */
interface Header {
  const CONTENTTYPE   = 'content-type';
  const CONTENTLENGTH = 'content-length';
  const DESTINATION   = 'destination';
  const SUBSCRIPTION  = 'subscription';
  const MESSAGEID     = 'message-id';
  const MESSAGE       = 'message';
  const RECEIPT       = 'receipt';
  const RECEIPTID     = 'receipt-id';
  const PERSISTENCE   = 'persistent';
  const TRANSACTION   = 'transaction';
  const VERSION       = 'version';
  const LOGIN         = 'login';
  const PASSCODE      = 'passcode';
  const ACCEPTVERSION = 'accept-version';
  const HOST          = 'host';
  const SELECTOR      = 'selector';
  const ACK           = 'ack';
  const ID            = 'id';
}