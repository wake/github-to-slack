<?php

  namespace App\Sender;

  use GuzzleHttp\Client;


  /**
   *
   * Web index
   *
   */
  class Slack {


    /**
     *
     * Lumen app
     *
     */
    protected $app;


    /**
     *
     * Slack endpoint
     *
     */
    protected $endpoint;


    /**
     *
     * Message text
     *
     */
    protected $text;


    /**
     *
     * Message attachments
     *
     */
    protected $attachment = [];


    /**
     *
     * Initial
     *
     */
    public function __construct ($app) {

      if (! isset ($_ENV['SLACK_ENDPOINT']))
        throw Exception ('Slack endpoint is needed');

      $this->app = $app;
      $this->endpoint = $_ENV['SLACK_ENDPOINT'];
      $this->token = $_ENV['SLACK_TOKEN'];
      $this->message = new Client ();

      return $this;
    }


    /**
     *
     * Text
     *
     */
    public function attachment (Array $content) {
      $this->attachment += $content;
      return $this;
    }


    /**
     *
     * Text
     *
     */
    public function text ($text) {
      $this->text = $text;
      return $this;
    }


    /**
     *
     * Set color
     *
     */
    public function _setColor ($color) {
      return $this->attachment (['color' => $color]);
    }


    /**
     *
     * Set action
     *
     */
    public function _setAction ($action) {
      return $this;
    }


    /**
     *
     * Set target
     *
     */
    public function _setTarget ($target) {

      $att = [];

      if (isset ($target['name']))
        $att['title'] = $target['name'];

      if (isset ($target['url']))
        $att['title_link'] = $target['url'];

      return $this->attachment ($att);
    }


    /**
     *
     * Set target
     *
     */
    public function _setOperator ($operator) {

      $att = [];

      if (isset ($operator['name']))
        $att['author_name'] = $operator['name'];

      if (isset ($operator['url']))
        $att['author_link'] = $operator['url'];

      if (isset ($operator['avatar']))
        $att['author_icon'] = $operator['avatar'];

      return $this->attachment ($att);
    }


    /**
     *
     * Set desc
     *
     */
    public function _setDescription ($desc = '') {
      return $this->attachment (['pretext' => $desc]);
    }


    /**
     *
     * Set desc
     *
     */
    public function _setSource ($source) {

      $att = [];

      if (isset ($source['name']))
        $att['footer'] = $source['name'];

      if (isset ($source['icon']))
        $att['footer_icon'] = $source['icon'];

      return $this->attachment ($att);
    }


    /**
     *
     * Initial
     *
     */
    public function send () {

      $body = [];

      if (isset ($this->text))
        $body['text'] = $this->text;

      if (count ($this->attachment) > 0)
        $body['attachments'] = [$this->attachment];

      $this->message->post ($this->endpoint, ['body' => json_encode ($body, JSON_UNESCAPED_UNICODE)]);

      return $this;
    }
  }
