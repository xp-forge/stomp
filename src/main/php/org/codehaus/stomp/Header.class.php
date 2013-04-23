<?php namespace org\codehaus\stomp;

interface Header {
  const CONTENTTYPE   = 'content-type';
  const CONTENTLENGTH = 'content-length';
  const DESTINATION   = 'destination';
  const SUBSCRIPTION  = 'subscription';
  const MESSAGEID     = 'message-id';
  const RECEIPT       = 'receipt';
  const RECEIPTID     = 'receipt-id';
  const PERSISTENCE   = 'persistence';
}