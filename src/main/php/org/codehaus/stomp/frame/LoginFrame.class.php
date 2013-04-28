<?php namespace org\codehaus\stomp\frame;

use \org\codehaus\stomp\Header;

/**
 * Login frame
 *
 */
class LoginFrame extends Frame {
  protected
    $user     = NULL,
    $pass     = NULL,
    $versions = NULL,
    $host     = NULL;

  /**
   * Constructor
   *
   * @param   string user
   * @param   string pass
   */
  public function __construct($user, $pass, $host= NULL, $versions= array('1.0', '1.1')) {
    if ($host && !$versions) {
      throw new \lang\IllegalArgumentException('Versions required when specifying hostname (stomp 1.1 feature)');
    }

    $this->user= $user;
    $this->pass= $pass;
    $this->host= $host;
    $this->setSupportedVersions($versions);
  }

  /**
   * Set supported STOMP versions
   *
   * @param   [:string] v list of supported versions
   */
  public function setSupportedVersions(array $versions) {
    foreach ($versions as $v) {
      if (strlen($v) == 0) {
        throw new \lang\IllegalArgumentException('Invalid protocol version: '.\xp::stringOf($v));
      }
    }

    $this->versions= $versions;
  }

  /**
   * Frame command
   *
   */
  public function command() {
    return 'CONNECT';
  }

  /**
   * Login frame followed by CONNECTED response
   *
   */
  public function requiresImmediateResponse() {
    return TRUE;
  }

  /**
   * Retrieve headers
   *
   * @return  <string,string>[]
   */
  public function getHeaders() {
    $hdrs= array();
    $hdrs[Header::ACCEPTVERSION]= implode(',', $this->versions);
    $hdrs[Header::HOST]= $this->host;

    if (NULL !== $this->user) {
      $hdrs[Header::LOGIN]= $this->user;

      if (NULL !== $this->pass) {
        $hdrs[Header::PASSCODE]= $this->pass;
      }
    }


    return array_merge($hdrs, parent::getHeaders());
  }
}
