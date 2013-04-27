<?php namespace org\codehaus\stomp;

/**
 * Ack modes
 *
 * @see   xp://org.codehaus.stomp.Connection#subscribe
 * @see   http://activemq.apache.org/maven/activemq-core/apidocs/org/apache/activemq/transport/stomp/Stomp.Headers.Subscribe.AckModeValues.html
 */
interface AckMode {
  const AUTO        = 'auto';
  const CLIENT      = 'client';
  const INDIVIDUAL  = 'client-individual';
}
