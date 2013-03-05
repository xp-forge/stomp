<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  $package= 'org.codehaus.stomp.unittest';

  uses(
    'util.cmd.Console',
    'util.log.Logger',
    'util.log.FileAppender',
    'peer.server.Server',
    'peer.server.ServerProtocol',
    'org.codehaus.stomp.frame.Frame'
  );
  
  /**
   * STOMP Server used by IntegrationTest. 
   *
   * @see   xp://org.codehaus.stomp.unittest.StompIntegrationTest
   */
  class org·codehaus·stomp·unittest·TestingServer extends Object {

    /**
     * Start server
     *
     * @param   string[] args
     */
    public static function main(array $args) {

      // Server frames
      ClassLoader::defineClass('org.codehaus.stomp.frame.ConnectFrame', 'org.codehaus.stomp.frame.Frame', array(), '{
        public function command() { return "CONNECT"; }
      }');

      $protocol= newinstance('peer.server.ServerProtocol', array(), '{
        protected $frames= NULL;
        protected $handlers= array();
        public $messages= array();

        public function initialize() { 
          $this->frames= Package::forName("org.codehaus.stomp.frame");
          $this->handlers= array(
            "CONNECT" => function($frame, $protocol) {
              if ("testtest" === $frame->getHeader("login").$frame->getHeader("passcode")) {
                return $protocol->frame("CONNECTED");
              } else {
                return $protocol->frame("ERROR");
              }
            },

            "SEND" => function($frame, $protocol) {
              if ("/queue/test" === $frame->getHeader("destination")) {
                $protocol->messages[]= $frame->getBody();
                return NULL;
              } else {
                return $protocol->frame("ERROR");
              }
            },

            "SUBSCRIBE" => function($frame, $protocol) {
              if ("/queue/test" === $frame->getHeader("destination")) {
                $i= 1;
                $messages= array();
                while ($dequeued= array_shift($protocol->messages)) {
                  $message= $protocol->frame("message");
                  $message->addHeader("message-id", $i++);
                  $message->setBody($dequeued);
                  $messages[]= $message;
                }
                return $messages;
              } else {
                return $protocol->frame("ERROR");
              }
            }
          );
        }

        public function handleConnect($socket) {
        }

        public function handleDisconnect($socket) {
        }

        public function frame($name) {
          return $this->frames->loadClass(ucfirst(strtolower($name))."Frame")->newInstance();
        }

        public function handleData($socket) {
          $line= $socket->readLine();

          if ("SHUTDOWN" === $line) {
            $this->server->terminate= TRUE;
            return;
          } else if ("" == $line) {
            // Nothing
            return;
          }

          $request= $this->frame($line);
          $request->fromWire(new StringReader($socket->getInputStream()));
          Console::writeLine("<<< ", $request);

          if ($handler= $this->handlers[$request->command()]) {
            $response= $handler($request, $this);
          } else {
            $response= $this->frame("ERROR");
          }

          Console::writeLine(">>> ", $response);
          if (NULL === $response) {
            return NULL;
          } else if (is_array($response)) {
            $out= new StringWriter($socket->getOutputStream());
            foreach ($reponse as $framne) {
              $frame->write($out);
            }
          } else {
            $response->write(new StringWriter($socket->getOutputStream()));
          }
        }

        public function handleError($socket, $e) {
        }
      }');

      $s= new Server('127.0.0.1', 0);
      try {
        $s->setProtocol($protocol);
        $s->init();
        Console::writeLinef('+ Service %s:%d', $s->socket->host, $s->socket->port);
        $s->service();
        Console::writeLine('+ Done');
      } catch (Throwable $e) {
        Console::writeLine('- ', $e->getMessage());
      }
    }
  }
?>
