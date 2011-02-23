<?php

# $sgconf["base"] = "http://example.org/mygloss/"

$sgconf["pdo"]  = "sqlite:data/example.sqlite";

$sgconf["title"] = "a simple glossary";
$sgconf["home"]  = "about"; // start page

$sgconf["permissions"]["all"]["view"]   = true;
$sgconf["permissions"]["all"]["edit"]   = false;
$sgconf["permissions"]["all"]["create"] = false;
$sgconf["permissions"]["all"]["list"]   = true;

?>
