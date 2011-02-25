<?php

# $sgconf["base"] = "http://example.org/mygloss/"

$sgconf["pdo"]  = "sqlite:data/example.sqlite";

$sgconf["title"] = "a simple glossary";
$sgconf["home"]  = "about"; // start page

$sgconf["permissions"]["all"]["view"]   = true;
$sgconf["permissions"]["all"]["edit"]   = false;
$sgconf["permissions"]["all"]["create"] = false;
$sgconf["permissions"]["all"]["list"]   = true;

# to allow file uploads you must have set 'file_uploads = On' in 'php.ini'
$sgconf["permissions"]["all"]["import"] = false;

?>
