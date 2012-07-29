<?php

// Errors on full!
ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

$dir = realpath(dirname(__FILE__));
defined('PROJECT_BASE') OR define('PROJECT_BASE', realpath($dir.'/../').'/');
defined('GS_LOAD') OR define('GS_LOAD', true);
