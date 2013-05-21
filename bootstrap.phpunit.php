<?php

define('ROOT_DIR', dirname(__FILE__));

require_once ROOT_DIR . '/vendor/autoload.php';
//
////Remove environment mode if set
//unset($_ENV['SLIM_MODE']);
//
////Reset session
//$_SESSION = array();
//
////Prepare default environment variables
//\Slim\Environment::mock(array(
//    'REQUEST_METHOD' => 'GET',
//    'REMOTE_ADDR' => '127.0.0.1',
//    'SCRIPT_NAME' => '', //<-- Physical
//    'PATH_INFO' => '/bar', //<-- Virtual
//    'QUERY_STRING' => 'one=1&two=2&three=3',
//    'SERVER_NAME' => 'slim',
//    'SERVER_PORT' => 80,
//    'slim.url_scheme' => 'http',
//    'slim.input' => '',
//    'slim.errors' => fopen('php://stderr', 'w'),
//    'HTTP_HOST' => 'slim'
//));