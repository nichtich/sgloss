<?php
/**
 * @file
 *
 * NEVER EDIT THIS FILE
 *
 * This file contains the configuration settings. If you make changes
 * here, they will be lost on the next upgrade. To customize your
 * installation, edit the file 'config-local.php' instead.
 */

$sgconf = array(
  "version" => "0.0.3",

  # Get current base URL
  "base" => 
      ( (!empty($_SERVER['HTTPS']) ? "https" : "http") 
      . "://"
      . ( empty( $_SERVER['SERVER_NAME'] ) ? 'localhost' : $_SERVER['SERVER_NAME']) 
      # TODO: add port
      . $_SERVER['PHP_SELF'] ),

  # default theme
  "theme" => "default",

  # default article
  "home" => "", # list all articles

  # title of the wiki
  "title" => "SGWiki",

  # permissions
  "permissions" => array(
    "all" => array( # default user group
      "view"   => true,
      "edit"   => true,
      "create" => true,
      "links"  => true,
      "list"   => true,
      "import" => true
    )
  ),

  # database connection
  "pdo" => "sqlite:data/example.sqlite"

);

?>
