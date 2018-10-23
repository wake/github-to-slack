<?php

  namespace App\Receiver;

  use GuzzleHttp\Client;
  use GuzzleHttp\Psr7;
  use GuzzleHttp\Exception\RequestException;


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
     * Parsed
     *
     */
    protected $parsed;


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
    protected $data = [];


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
    protected $allowModes = ['project', 'project_column', 'project_card', 'pages'];


    /**
     *
     * Initial
     *
     */
    public function __construct ($app) {

      $this->app = $app;
      $this->token = $_ENV['GITHUB_TOKEN'];
      $this->api = new Client ();

      // Set source
      $this->data['source'] = [
        'name' => 'Github Webhook',
        'icon' => $app->request->fullUrl () . '/assets/logo/GitHub-Mark-32px.png'
      ];

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
      $this->parsed = false;

      if (! $this->payload)
        throw new \Exception ("Error parsing input", 1);

      foreach ($this->allowModes as $mode) {
        if (isset ($this->payload[$mode]))
          $this->mode = $mode;
      }

      if (! $this->mode)
        return $this;

      $parser = '_parse' . strtr (ucwords (strtr ($this->mode, ['_' => ' '])), [' ' => '']);

      try {
        $this->$parser ();
        $this->parsed = true;
      }

      catch (Exception $e) {
        //
      }

      return $this;
    }


    /**
     *
     * Parse project
     *
     */
    protected function _parsePages () {

      $pl = $this->payload;

      $this->data = [

        'action' => $pl['pages'][0]['action'],

        'target' => [
          'name' => $pl['pages'][0]['title'],
          'url' => $pl['pages'][0]['html_url']
        ],

        'operator' => [
          'name' => $pl['sender']['login'],
          'url' => $pl['sender']['html_url'],
          'avatar' => $pl['sender']['avatar_url'],
        ],

        'description' => [
          'text' => "Wiki page {$pl['pages'][0]['title']} {$pl['pages'][0]['action']} by {$pl['sender']['login']} in {$pl['repository']['full_name']}",
          'mrkdwn' => "Wiki page *<{$pl['pages'][0]['html_url']}|{$pl['pages'][0]['title']}>* {$pl['pages'][0]['action']} by *{$pl['sender']['login']}*"
        ],

        'source' => [
          'name' => [
            'text' => $this->data['source']['name'],
            'mrkdwn' => "*<{$pl['repository']['html_url']}|{$pl['repository']['full_name']}>* | Github Webhook"
          ]
        ]
      ] + $this->data;

      return $this;
    }


    /**
     *
     * Parse project
     *
     */
    protected function _parseProject () {

      $pl = $this->payload;

      $this->data = [

        'action' => $pl['action'],

        'target' => [
          'name' => $pl['project']['name'],
          'url' => $pl['project']['html_url']
        ],

        'operator' => [
          'name' => $pl['sender']['login'],
          'url' => $pl['sender']['html_url'],
          'avatar' => $pl['sender']['avatar_url'],
        ],

        'description' => [
          'text' => "Project {$pl['project']['name']} {$pl['action']} by {$pl['sender']['login']} in {$pl['repository']['full_name']}",
          'mrkdwn' => "Project *<{$pl['project']['html_url']}|{$pl['project']['name']}>* {$pl['action']} by *{$pl['sender']['login']}*"
        ],

        'source' => [
          'name' => [
            'text' => $this->data['source']['name'],
            'mrkdwn' => "*<{$pl['repository']['html_url']}|{$pl['repository']['full_name']}>* | Github Webhook"
          ]
        ]
      ] + $this->data;

      return $this;
    }


    /**
     *
     * Parse project
     *
     */
    protected function _parseProjectColumn () {

      $pl = $this->payload;

      $curl = '';
      $desc = "Column {$pl['action']} by {$pl['sender']['login']}";

      // Load project
      if ($project = $this->getProject ($pl['project_column']['project_url'])) {
        $curl = "{$project['html_url']}#column-{$pl['project_column']['id']}";
        $desc = [
          'text' => $desc,
          'mrkdwn' => "Column *<$curl|{$pl['project_column']['name']}>* {$pl['action']} by *{$pl['sender']['login']}*"
            . " in project *<{$project['html_url']}|{$project['name']}>*"
        ];
      }

      $this->data = [

        'action' => $pl['action'],

        'target' => [
          'name' => $pl['project_column']['name'],
          'url' => $curl
        ],

        'operator' => [
          'name' => $pl['sender']['login'],
          'url' => $pl['sender']['html_url'],
          'avatar' => $pl['sender']['avatar_url'],
        ],

        'description' => $desc,

        'source' => [
          'name' => [
            'text' => $this->data['source']['name'],
            'mrkdwn' => "*<{$pl['repository']['html_url']}|{$pl['repository']['full_name']}>* | Github Webhook"
          ]
        ]
      ] + $this->data;

      return $this;
    }


    /**
     *
     * Parse project
     *
     */
    protected function _parseProjectCard () {

      $pl = $this->payload;
      $action = "_parseProjectCard" . ucfirst ($pl['action']);

      return $this->$action ();
    }


    /**
     *
     * Parse project
     *
     */
    protected function _parseProjectCardCreated () {

      $pl = $this->payload;

      $curl = '';
      $desc = "Card {$pl['action']} by {$pl['sender']['login']}\n{$pl['project_card']['note']}";

      // Load project
      if ($project = $this->getProject ($pl['project_card']['project_url'])) {
        $curl = "{$project['html_url']}#card-{$pl['project_card']['id']}";
        $desc = [
          'text' => $desc,
          'mrkdwn' => "Card *<$curl|" . mb_substr ($pl['project_card']['note'], 0, 10) . "...>* {$pl['action']} by *{$pl['sender']['login']}*"
            . " in project *<{$project['html_url']}|{$project['name']}>*"
        ];
      }

      $this->data = [

        'action' => $pl['action'],

        'target' => [
          'name' => $pl['project_card']['note'],
          'url' => $curl
        ],

        'operator' => [
          'name' => $pl['sender']['login'],
          'url' => $pl['sender']['html_url'],
          'avatar' => $pl['sender']['avatar_url'],
        ],

        'paragraph' => [
           'title' => '',
           'text' => ":pencil:\n" . $pl['project_card']['note'] . "\n--\n<$curl|View Card>",
           'short' => false
        ],

        'description' => $desc,

        'source' => [
          'name' => [
            'text' => $this->data['source']['name'],
            'mrkdwn' => "*<{$pl['repository']['html_url']}|{$pl['repository']['full_name']}>* | Github Webhook"
          ]
        ]
      ] + $this->data;

      return $this;
    }


    /**
     *
     * Parse project
     *
     */
    protected function _parseProjectCardDeleted () {
      return $this->_parseProjectCardCreated ();
    }


    /**
     *
     * Parse project
     *
     */
    protected function _parseProjectCardEdited () {

      $pl = $this->payload;

      $diff = new \Diff (explode("\n", $pl['changes']['note']['from']), explode("\n", $pl['project_card']['note']), []);
      $ctxt = $diff->render (new \App\Helper\DiffRendererSplitContext ());

      $ori = $ctxt['a'];
      $new = $ctxt['b'];

      //print_r ($diff->getGroupedOpcodes ());
      //print_r (get_class_methods ($diff));
      //print_r ($diff->render (new \App\Helper\DiffRendererSplitContext ()));
      //die;

      $curl = '';
      $desc = "Card {$pl['action']} by {$pl['sender']['login']}\n{$pl['project_card']['note']}";

      // Load project
      if ($project = $this->getProject ($pl['project_card']['project_url'])) {
        $curl = "{$project['html_url']}#card-{$pl['project_card']['id']}";
        $desc = [
          'text' => $desc,
          'mrkdwn' => "Card *<$curl|" . mb_substr ($pl['project_card']['note'], 0, 10) . "...>* {$pl['action']} by *{$pl['sender']['login']}*"
            . " in project *<{$project['html_url']}|{$project['name']}>*"
        ];
      }

      $this->data = [

        'action' => $pl['action'],

        'target' => [
          'name' => $pl['project_card']['note'],
          'url' => $curl
        ],

        'operator' => [
          'name' => $pl['sender']['login'],
          'url' => $pl['sender']['html_url'],
          'avatar' => $pl['sender']['avatar_url'],
        ],

        'paragraphs' => [[

          'title' => '',
          'text' => ":arrow_right: _Original_ \n" . implode ("\n", $ori),
          'short' => true

        ], [

          'title' => '',
          'text' => ":ballot_box_with_check: _Edited_ \n" . implode ("\n", $new),
          'short' => true

        ]],

        'description' => $desc,

        'source' => [
          'name' => [
            'text' => $this->data['source']['name'],
            'mrkdwn' => "*<{$pl['repository']['html_url']}|{$pl['repository']['full_name']}>* | Github Webhook"
          ]
        ]
      ] + $this->data;

      return $this;
    }


    /**
     *
     * Parse project
     *
     */
    protected function _parseProjectCardMoved () {

      $pl = $this->payload;

      $curl = '';
      $desc = "Card {$pl['action']} by {$pl['sender']['login']}\n{$pl['project_card']['note']}";

      $fromColumn = $this->getColumn ('https://api.github.com/projects/columns/' . $pl['changes']['column_id']['from']);
      $toColumn = $this->getColumn ($pl['project_card']['column_url']);

      // Load project
      if ($project = $this->getProject ($pl['project_card']['project_url'])) {

        $curl = "{$project['html_url']}#card-{$pl['project_card']['id']}";
        $fromUrl = "{$project['html_url']}#column-{$fromColumn['id']}";
        $toUrl = "{$project['html_url']}#column-{$toColumn['id']}";

        $desc = [
          'text' => $desc,
          'mrkdwn' => "Card *<$curl|" . mb_substr ($pl['project_card']['note'], 0, 10) . "...>* {$pl['action']} by *{$pl['sender']['login']}*"
            . " from column *<{$fromUrl}|{$fromColumn['name']}>* to *<{$toUrl}|{$toColumn['name']}>*"
            . " in project *<{$project['html_url']}|{$project['name']}>*"
        ];
      }

      $this->data = [

        'action' => $pl['action'],

        'target' => [
          'name' => $pl['project_card']['note'],
          'url' => $curl
        ],

        'operator' => [
          'name' => $pl['sender']['login'],
          'url' => $pl['sender']['html_url'],
          'avatar' => $pl['sender']['avatar_url'],
        ],

        'description' => $desc,

        'source' => [
          'name' => [
            'text' => $this->data['source']['name'],
            'mrkdwn' => "*<{$pl['repository']['html_url']}|{$pl['repository']['full_name']}>* | Github Webhook"
          ]
        ]
      ] + $this->data;

      return $this;
    }


    /**
     *
     * Parse project
     *
     */
    protected function getProject ($url) {

      try {

        $resp = $this->api->get ($url, [
          'headers' => [
            'Accept' => 'application/vnd.github.inertia-preview+json',
            'Authorization' => "token {$this->token}"
          ]
        ]);

        return json_decode ($resp->getBody ()->getContents (), true);
      }

      catch (RequestException $e) {
      }

      return null;
    }


    /**
     *
     * Parse project
     *
     */
    protected function getColumn ($url) {

      try {

        $resp = $this->api->get ($url, [
          'headers' => [
            'Accept' => 'application/vnd.github.inertia-preview+json',
            'Authorization' => "token {$this->token}"
          ]
        ]);

        return json_decode ($resp->getBody ()->getContents (), true);
      }

      catch (RequestException $e) {
      }

      return ['name' => '', 'id' => ''];
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

      if ($this->sender && $this->parsed)
        $this->sender->send ();

      return $this;
    }
  }
