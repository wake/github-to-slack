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
      $this->attachment = $content + $this->attachment;
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
      return $this;

      $att = [];

      if (isset ($target['name']))
        $att['text'] = $target['name'];

      if (isset ($target['url']))
        $att['text_link'] = $target['url'];

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

      if (is_string ($desc))
        return $this->attachment (['text' => $desc]);

      else if (isset ($desc['mrkdwn']))
        return $this->attachment (['text' => $desc['mrkdwn'], 'mrkdwn_in' => ['text', 'pretext']]);

      else if (isset ($desc['text']))
        return $this->attachment (['text' => $desc['text']]);

      return $this;


      // Dropped below
      if (is_string ($desc))
        return $this->attachment (['pretext' => $desc]);

      else if (isset ($desc['mrkdwn']))
        return $this->attachment (['pretext' => $desc['mrkdwn'], 'mrkdwn_in' => ['text', 'footer', 'pretext']]);

      else if (isset ($desc['text']))
        return $this->attachment (['pretext' => $desc['text']]);

      return $this;
    }


    /**
     *
     * Set desc
     *
     */
    public function _setParagraphs ($paragraphs = []) {

      foreach ($paragraphs as $paragraph)
        $this->_setParagraph ($paragraph);

      return $this;
    }


    /**
     *
     * Set desc
     *
     */
    public function _setParagraph ($paragraph = []) {

      $att = [];

      if (isset ($paragraph['title']))
        $att['title'] = $paragraph['title'];

      if (isset ($paragraph['text']))
        $att['value'] = $paragraph['text'];

      if (isset ($paragraph['short']))
        $att['short'] = $paragraph['short'];

      $fields = [];

      if (isset ($this->attachment['fields']))
        $fields = $this->attachment['fields'];

      $fields[] = $att;

      return $this->attachment (['fields' => $fields]);
    }


    /**
     *
     * Set desc
     *
     */
    public function _setSource ($source) {

      $att = [];

      if (is_string ($source['name']))
        $att['footer'] = $source['name'];

      else if (isset ($source['name']['mrkdwn']))
        $att['footer'] = $source['name']['mrkdwn'];

      else if (isset ($source['name']['text']))
        $att['footer'] = $source['name']['text'];

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

      $this->_setColor ('#51A1CB');

      $body = [];

      if (isset ($this->text))
        $body['text'] = $this->text;

      if (count ($this->attachment) > 0)
        $body['attachments'] = [$this->attachment];

      $this->message->post ($this->endpoint, ['body' => json_encode ($body, JSON_UNESCAPED_UNICODE)]);

      return $this;
    }
  }
