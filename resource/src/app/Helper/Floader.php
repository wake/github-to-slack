<?php

  namespace App\Helper;


  /**
   *
   * File loader
   *
   */
  class Floader {


    /**
     *
     * Pass values
     *
     */
    static $pack = [];


    /**
     *
     * Inject data
     *
     */
    static function inject ($compact) {
      static::$pack = $compact;
    }


    /**
     *
     * Seek for files and pathes
     *
     */
    static function seek ($file, $path) {

      if (! is_array ($file))
        static::load ($file, $path);

      else {

        foreach ($file as $p => $f) {

          if (is_array ($f))
            static::seek ($f, "$path/$p");

          else
            static::load ($f, $path);
        }
      }
    }


    /**
     *
     * Load file
     *
     */
    static function load ($file, $path) {
      extract (static::$pack) && file_exists ("$path/$file.php") && require_once "$path/$file.php";
    }


    /**
     *
     * Autoload
     *
     */
    static function autoload ($path, $recursive = true) {

      if (! is_dir ($path))
        return;

      extract (static::$pack);

      $ls = scandir ($path);

      foreach ($ls as $file) {

        if ($file == '.' || $file == '..')
          continue;

        if (is_dir ("$path/$file") && $recursive)
          static::autoload ("$path/$file", $recursive);

        else if (pathinfo ("$path/$file", PATHINFO_EXTENSION) == 'php')
          require_once "$path/$file";
      }
    }
  }
