<?php namespace org\codehaus\stomp;

interface Header {
  const CONTENTTYPE   = 'content-type';
  const DESTINATION   = 'destination';
  const SUBSCRIPTION  = 'subscription';
  const CONTENTLENGTH = 'content-length';
  const MESSAGEID     = 'message-id';
}