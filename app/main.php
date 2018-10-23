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
  $router->get ('/', ['as' => 'index', function (Request $request) use ($app) {

    return 'Webhook Hub';

  }]);


  /**
   *
   * Proxy
   *
   */
  $router->post ('/github/to/slack', ['as' => 'proxy', function () use ($app) {

    $receiver = new Receiver\Github ($app);
    $receiver->parseInput ()->proxy (new Sender\Slack ($app))->send ();

    return '';

  }]);
