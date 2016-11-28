<?php
/*
 *  Index page for User Management App
 *
 */
 
// Set up
include_once 'autoload.php';
include_once 'config/settings.php';
$db = new DB($settings);

$m = new UserModel($db);
$c = new UserController($m);
$v = new UserView($c, $m);

//  cli or http
if (PHP_SAPI === 'cli') {
    $args = $argv;
    array_shift($args);
    $uri = "/__FILE__?" . implode('&', $args);
} else {
    $uri = $_SERVER["REQUEST_URI"];
}

//  Get route
$c->parseRoute($uri);

//  Execute and
$c->executeAction();

//  Output
echo $v->render();
