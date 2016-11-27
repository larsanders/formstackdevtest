<?php

/*
 *  Index page for User Management App
 *
 */
 
// Set up
include_once 'src/autoload.php';
include_once 'config/settings.php';
$db = new DB($settings);

$m = new UserModel($db);
$c = new UserController($m);
$v = new UserView($c, $m);

//  Get route
$c->parseRoute($_SERVER["REQUEST_URI"]);

//  Execute and
$c->executeAction();

//  Output
echo $v->render();
