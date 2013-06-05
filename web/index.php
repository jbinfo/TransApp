<?php

ini_set("display_errors", 0);

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use TransApp\Application;

// instantiate the Application
$app = new Application($request = Request::createFromGlobals(), 'prod');

// run the Application and return a response
$app->run($request);
