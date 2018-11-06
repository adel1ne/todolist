<?php
ini_set("display_errors",1);
error_reporting(E_ALL);

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', __DIR__);
define('BASEPATH', 'system');
define('VIEWS_PATH', ROOT.DS.'views');
define('WIDGETS_PATH', ROOT.DS.'widgets');

session_start();

include('vendor/autoload.php');

require_once(ROOT.DS.BASEPATH.DS.'init.php');
\system\App::run($_SERVER['REQUEST_URI']);

