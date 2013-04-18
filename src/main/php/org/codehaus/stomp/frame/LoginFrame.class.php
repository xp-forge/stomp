<?php namespace org\codehaus\stomp\frame;

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
    public function __construct($user, $pass, $host= NULL, $versions= NULL) {
      if ($host && !$versions) {
        throw new \lang\IllegalArgumentException('Versions required when specifying hostname (stomp 1.1 feature)');
      }

      $this->user= $user;
      $this->pass= $pass;
      $this->host= $host;
      if ($versions) $this->setSupportedVersions($versions);
    }

    /**
     * Set supported STOMP versions
     *
     * @param   [:string] v list of supported versions
     */
    public function setSupportedVersions(array $v) {
      $this->versions= $v;
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
      $hdrs= array(
        'login'     => $this->user,
        'passcode'  => $this->pass
      );

      if ($this->versions) {
        $hdrs['accept-version']= implode(',', $this->versions);
      }

      if ($this->host) {
        $hdrs['host']= $this->host;
      }

      return array_merge($hdrs, parent::getHeaders());
    }
  }
?>
