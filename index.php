<?php
// setup
include 'src/autoload.php';
include 'config/settings.php';
$db = new DB($settings);

$m = new UserModel($db);
$c = new UserController($m);
$v = new UserView($c, $m);

//  get route
$uri = $_SERVER["REQUEST_URI"];
$c->parseRoute($uri);

//  execute
if(isset($c->action) && !empty($c->action)){
    $c->executeAction();
    $output = $v->render();
} else {
    $output = $v->renderIndex($m->format);
}

//  output
echo $output;
