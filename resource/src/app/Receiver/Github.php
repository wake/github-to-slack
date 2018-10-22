<?php

  namespace App\Receiver;

  use GuzzleHttp\Client;


  /**
   *
   * Github weebhook handler
   *
   */
  class Github {


    /**
     *
     * Lumen app
     *
     */
    protected $app;


    /**
     *
     * Token
     *
     */
    protected $token;


    /**
     *
     * Payload
     *
     */
    protected $payload;


    /**
     *
     * Data
     *
     */
    protected $data = [
      'source' => [
        'name' => 'Github Webhook',
        'icon' => ''
      ]
    ];


    /**
     *
     * Mode
     *
     */
    protected $mode;


    /**
     *
     * Allow modes
     *
     */
    protected $allowModes = ['project'];//, 'project_column', 'project_card', 'gollum'];


    /**
     *
     * Initial
     *
     */
    public function __construct ($app) {

      $this->app = $app;
      $this->token = $_ENV['GITHUB_TOKEN'];

      return $this;
    }


    /**
     *
     * Parse
     *
     */
    public function parseInput () {

      // fetch input
      $this->payload = json_decode (file_get_contents ('php://input'), true);

      if (! $this->payload)
        throw new \Exception ("Error parsing input", 1);

      foreach ($this->allowModes as $mode) {
        if (isset ($this->payload[$mode]))
          $this->mode = $mode;
      }

      $parser = '_parse' . strtr (ucwords (strtr ($mode, '_', ' ')), ' ', '');
      $data = $this->$parser ();

      return $this;
    }


    /**
     *
     * Parse project
     *
     */
    protected function _parseProject () {

      $pl = $this->payload;

      $this->data += [

        'action' => $pl['action'],

        'target' => [
          'name' => $pl['project']['name'],
          'url' => $pl['project']['url']
        ],

        'operator' => [
          'name' => $pl['sender']['login'],
          'url' => $pl['sender']['url'],
          'avatar' => $pl['sender']['avatar_url'],
        ],

        'description' => "Project {$pl['action']} by {$pl['sender']['login']}"
      ];

      return $this;
    }


    /**
     *
     * Parse project
     *
     */
    protected function _parseProjectColumn () {

      $pl = $this->payload;

      $this->data += [

        'action' => $pl['action'],

        'target' => [
          'name' => $pl['project_column']['name'],
          'url' => $pl['project_column']['url']
        ],

        'operator' => [
          'name' => $pl['sender']['login'],
          'url' => $pl['sender']['url'],
          'avatar' => $pl['sender']['avatar_url'],
        ],

        'description' => "Project column {$pl['action']} by {$pl['sender']['login']}"
      ];

      return $this;
    }


    /**
     *
     * Proxy to sender
     *
     */
    public function proxy ($sender) {

      foreach ($this->data as $type => $data) {
        $setter = '_set' . strtr (ucwords (strtr ($type, '_', ' ')), ' ', '');
        $sender->$setter ($data);
      }

      $this->sender = $sender;

      return $this;
    }


    /**
     *
     * Send
     *
     */
    public function send () {

      if ($this->sender)
        $this->sender->send ();

      return $this;
    }
  }
