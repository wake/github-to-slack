<?php


  /**
   *
   * DB config
   *
   */

  /**
   *
   * Paris & Idiorm
   *
   */
  ORM::configure ('mysql:host='. $_ENV['DB_HOST'] .';dbname='. $_ENV['DB_NAME']);
  ORM::configure ('username', $_ENV['DB_USER']);
  ORM::configure ('password', $_ENV['DB_PASS']);

  ORM::configure ('logging',  $_ENV['DB_LOGGING']);
  ORM::configure ('caching',  $_ENV['DB_CACHING']);
  ORM::configure ('caching_auto_clear', $_ENV['DB_CACHING_AUTO_CLEAR']);

  ORM::configure ('driver_options', array (PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '. $_ENV['DB_CHARSET']));
