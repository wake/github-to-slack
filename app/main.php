<?php


  use Illuminate\Http\Request;
  use Illuminate\Validation\ValidationException;


  /**
   *
   * Web index
   *
   */
  $router->get ('/', ['as' => 'index', function () use ($app) {

    return view ('index')
      ->with ('version', $app->version ());

  }]);
