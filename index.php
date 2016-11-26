<?php
include 'src/autoload.php';

include 'config/settings.php';
$db = new DB($settings);

$m = new UserModel($db);
$c = new UserController($m);
$v = new UserView($c, $m);

    /** 
     *  VALID ROUTES
     * 
     *  @todo create router class
     *  
     *  General form
     *  index.php/action/key/value/key/value
     *  index.php/action/id/int
     *  
     *  index.php/create/email/a@b.io/first_name/foo/last_name/bar/password/8c88#SW1
     *  index.php?action=create&email=a@b.io&first_name=foo&last_name=bar&password=8c88#SW1
     *
     *  index.php/update/id/8/first_name/jerry
     *  index.php?action=update&id=8&first_name=jerry
     *  
     *  index.php/delete/id/8
     *  index.php?action=delete&id=8
     *  
     */

//  Convert route to parameters
$params = [];
$uri = $_SERVER["REQUEST_URI"];
$parsed = parse_url($uri);

if($parsed['path'] == $_SERVER['PHP_SELF']){
    /** @todo add Router class here $router->parseQuery */
    // query string
    $uri = parse_url($uri);
    parse_str($uri['query'], $params);
    $action = $params['action'];
    unset($params['action']);
    if(isset($params['id'])){
        $id = (int)$params['id'];
        unset($params['id']);
    }
} else {
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
        $action_error = 'Unrecognized action. Available actions = | ';
        foreach($c->actions as $a){
            $action_error .= $a.' | ';
        }
        die($action_error);
    }
    //  sort data into params array and id
    for($i=0; $i < count($uri_chunks); $i++){
        if(in_array($uri_chunks[$i], $m->fields)){
            //  extra vars here just for clarity
            $key = $uri_chunks[$i];
            $val = $uri_chunks[$i+1];
            $params[$key] = $val;
            $i++;
        } elseif($uri_chunks[$i] == 'id') {
            $id = (int)$uri_chunks[$i+1];
            $i++;
        }
    }
}

if(empty($params) && $action != 'showall' && $action != 'delete'){
    die('Missing parameters for this action.');
}

//  execute action
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
