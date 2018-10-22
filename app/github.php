<?php


  use Illuminate\Http\Request;
  use Illuminate\Validation\ValidationException;
  use App\Receiver;
  use App\Sender;


  /**
   *
   * Web index
   *
   */
  $router->post ('/github', ['as' => 'github', function () use ($app) {

    $receiver = new Receiver\Github ($app);
    $receiver->parseInput ()->proxy (new Sender\Slack ($app))->send ();

    return '';

  }]);
