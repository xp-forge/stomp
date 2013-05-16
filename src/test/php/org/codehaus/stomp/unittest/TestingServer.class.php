<?php namespace org\codehaus\stomp\unittest;

use util\cmd\Console;
use util\log\LogCategory;
use util\log\FileAppender;
use peer\server\Server;

/**
 * STOMP Server used by IntegrationTest. 
 *
 * @see   xp://org.codehaus.stomp.unittest.StompIntegrationTest
 */
class TestingServer extends \lang\Object {

  /**
   * Start server
   *
   * @param   string[] args
   */
  public static function main(array $args) {

    // Define server frame
    \lang\XPClass::forName('peer.stomp.frame.Frame');
    \lang\ClassLoader::defineClass('peer.stomp.frame.ConnectFrame', 'peer.stomp.frame.Frame', array(), '{
      public function command() { return "CONNECT"; }
    }');

    // Define protocol
    $protocol= newinstance('peer.server.ServerProtocol', array(), '{
      protected $frames= null;
      protected $handlers= array();
      protected $cat= null;
      public $messages= array();

      public function initialize() { 
        $this->frames= Package::forName("peer.stomp.frame");
        $this->handlers= array(
          "CONNECT" => function($frame, $protocol) {
            $protocol->messages= array();
            if ("testtest" === $frame->getHeader("login").$frame->getHeader("passcode")) {
              return $protocol->frame("CONNECTED");
            } else {
              return $protocol->frame("ERROR");
            }
          },

          "DISCONNECT" => function($frame, $protocol) {
            return null;
          },

          "SEND" => function($frame, $protocol) {
            if ("/queue/test" === $frame->getHeader("destination")) {
              $protocol->messages[]= $frame->getBody();
              if (!$frame->hasHeader("receipt")) return null;

              $receipt= $protocol->frame("RECEIPT");
              $receipt->addHeader("receipt-id", $frame->getHeader("receipt"));
              return $receipt;
            } else {
              return $protocol->frame("ERROR");
            }
          },

          "ACK" => function($frame, $protocol) {
            return null;
          },

          "SUBSCRIBE" => function($frame, $protocol) {
            if ("/queue/test" === $frame->getHeader("destination")) {
              $i= 1;
              $messages= array();
              while ($dequeued= array_shift($protocol->messages)) {
                $message= $protocol->frame("message");
                $message->addHeader("message-id", $i++);
                $message->addHeader("destination", $frame->getHeader("destination"));
                $message->addHeader("subscription", $frame->getHeader("id"));
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
        $this->cat && $this->cat->debug("Connect", $socket);
      }

      public function handleDisconnect($socket) {
        $this->cat && $this->cat->debug("Disconnect", $socket);
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
        $this->cat && $this->cat->info("<<<", $request);

        if ($handler= $this->handlers[$request->command()]) {
          try {
            $response= $handler($request, $this);
          } catch (Throwable $e) {
            $this->cat && $this->cat->warn("*** ", $e);
            $response= $this->frame("ERROR");
          }
        } else {
          $response= $this->frame("ERROR");
        }

        $this->cat && $this->cat->info(">>>", $response);
        if (null === $response) {
          return null;
        } else if (is_array($response)) {
          $out= new StringWriter($socket->getOutputStream());
          foreach ($response as $frame) {
            $frame->write($out);
          }
        } else {
          $response->write(new StringWriter($socket->getOutputStream()));
        }
      }

      public function handleError($socket, $e) {
      }

      public function setTrace($cat) {
        $this->cat= $cat;
      }
    }');

    // Trace file
    if (isset($args[0])) {
      $protocol->setTrace(create(new LogCategory())->withAppender(new FileAppender($args[0])));
    }

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