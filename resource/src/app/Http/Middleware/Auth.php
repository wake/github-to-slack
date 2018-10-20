<?php


  namespace App\Http\Middleware;

  use Closure;


  /**
   *
   * User auth
   *
   */
  class Auth {


    /**
     *
     * Parameters
     *
     */
    protected $login;
    protected $user;


    /**
     *
     * Construct
     *
     */
    public function __construct () {
    }


    /**
     *
     * Handle an incoming request.
     *
     */
    public function handle ($request, Closure $next, $guard = null) {

      $login = $request->session ()->get ('login');
      $user = $request->session ()->get ('user');

      if (! $login || is_null ($user))
        return redirect ('/#');

      return $next ($request);
    }
  }
