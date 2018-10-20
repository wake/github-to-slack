<?php


if (! function_exists ('config_path')) {


  /**
   * Get the configuration path.
   *
   * @param  string $path
   * @return string
   */
  function config_path ($path = '') {
    return app()->basePath() . '/config' . ($path ? '/' . $path : $path);
  }
}


if (! function_exists ('sess')) {


  /**
   * Get session instance.
   *
   * @return object
   */
  function sess () {
    return app ('session');
  }
}
