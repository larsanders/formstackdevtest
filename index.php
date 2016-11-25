<?php

//require_once dirname(__FILE__) . '/setup.php';

//DB setup
include 'db.php';
include 'UserModel.php';
include 'UserController.php';
include 'UserView.php';
include 'settings.php';
$db = new DB($settings);


$m = new UserModel($db);
$c = new UserController($m);
$v = new UserView($c, $m);


$uri = $_SERVER["REQUEST_URI"];

//echo 'uri = '.$uri.'<br>';

    // ROUTES
    // index.php/create/email/a@b.io/first_name/foo/last_name/bar/password/8c88#SW1
    // index.php?action=create&email=a@b.io&first_name=foo&last_name=bar&password=8c88#SW1
    
    // index.php/update/id/8/first_name/jerry
    // index.php?action=update&id=8&first_name=jerry
    
    // index.php/delete/id/8
    // index.php/action/key/value/key/value
    
    // if( uri contains ?) {get} (else) {explode}

//  prep parameters
$params = [];

if(stripos($uri, '?') == 0){
    // route
    $uri_chunks = explode('/', $uri);
    if($uri_chunks[0] == ''){
        array_shift($uri_chunks);
    }
    // find valid action
    if(in_array( $uri_chunks[0], $c->actions )){
        $action = $uri_chunks[0];
        array_shift($uri_chunks);
    } else {
        /*
         *  @todo throw error when action is malformed
         */
        $action_error = 'Unrecognized action. Available actions = | ';
        foreach($c->actions as $a){
            $action_error .= $a.' | ';
        }
        die($action_error);
    }
    for($i=0; $i < count($uri_chunks); $i++){
        if(in_array($uri_chunks[$i], $m->fields)){
            //extra vars here just for clarity
            $key = $uri_chunks[$i];
            $val = $uri_chunks[$i+1];
            $params[$key] = $val;
            $i++;
        } elseif ($uri_chunks[$i] == 'id') {
            $id = (int)$uri_chunks[$i+1];
            $i++;
        }
    }
} else {
    // query string
    $uri = parse_url($uri);
    parse_str($uri['query'], $params);
    $action = $params['action'];
    unset($params['action']);
    if(isset($params['id'])){
        $id = (int)$params['id'];
        unset($params['id']);
    }
}
/*
var_dump($params);
var_dump($id);
var_dump($action);
//*/
if(empty($params) && $action != 'showall' && $action != 'delete'){
    die('Missing parameters for this action.');
}

//  take action
$format = 'html';
if (isset($action) && !empty($action)) {
    switch($action){
        case 'create':
            $c->createUser($params);
        break;
        case 'update':
            $c->updateUser($params, $id);
        break;
        case 'delete':
            $c->deleteUser($id);
        break;
        case 'showall':
            $result = $c->showAllUsers();
            $format = 'table';
        break;
    }
}

echo $v->render($format);
