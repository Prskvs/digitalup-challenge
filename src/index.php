<?php
//set timezone
date_default_timezone_set('Europe/Athens');
//set website root url
define('_ROOT', 'localhost/digitalup');
//set database conection parameters
define('_DBHOST', 'localhost');
define('_DBNAME', 'digitalup');
define('_DBUSER', 'root');
define('_DBPASS', '');

//load required files
require 'App/Controllers/Account.php';
require 'App/Models/User.php';
require 'App/App.php';

//start session
session_start();

//initialize application
$app = new App\App();
$app->init();
?>
