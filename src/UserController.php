<?php

/**
 * Show a list of all of the application's users.
 *
 * @return Response
 *
 */
class UserController
{
    private $m;
    
    public $action;
    
    public $actions = ['create', 'update', 'delete', 'showall'];
    
    public $formats = ['html', 'json'];

    public $params = [];
    
    public $id = 0;
    
    public function __construct($m)
    {
        $this->m = $m;
    }

    /** 
     *  Extracts parameters from url routes
     *  
     *  General forms
     *  index.php/action/key/value/key/value
     *  index.php/action/id/int
     *
     *  index.php/create/email/a@b.io/first_name/foo/last_name/bar/password/8c88*SW1
     *  index.php?action=create&email=a@b.io&first_name=foo&last_name=bar&password=8c88*SW1
     *
     *  index.php/update/id/8/first_name/jerry
     *  index.php?action=update&id=8&first_name=jerry
     *
     *  index.php/delete/id/8
     *  index.php?action=delete&id=8
     *
     *  @param $uri     URL of the request
     *
     *  @todo create separate Router class
     *
     */
    public function parseRoute($uri)
    {
        $params = [];
        $parsed = parse_url($uri);
        $id = 0;
        
        //  extract parameters from route
        if(isset($parsed['query']))
        {
            // query string
            parse_str($parsed['query'], $params);
            //  strip out action
            if(isset($params['action'])){
                if(in_array($params['action'], $this->actions)){
                    $action = $params['action'];
                    unset($params['action']);
                }
            }
            //  strip out id, cast to int
            if(isset($params['id'])){
                $id = (int)$params['id'];
                unset($params['id']);
            }
            //  strip out format
            if(isset($params['format'])){
                if(in_array($params['format'], $this->formats)){
                    $format = $params['format'];
                    unset($params['format']);
                }
            }
        } else {
            // route
            $uri_chunks = explode('/', $parsed['path']);
            //  sort data into params array and id
            for($i=0; $i < count($uri_chunks); $i++){
                if($uri_chunks[$i] == '' || $uri_chunks[$i] == 'index.php'){
                    continue;
                }
                if(in_array($uri_chunks[$i], $this->actions)){
                    $action = $uri_chunks[$i];
                    continue;
                } 
                if(in_array($uri_chunks[$i], $this->m->fields)){
                    //  extra vars here for readability
                    $key = $uri_chunks[$i];
                    $val = $uri_chunks[$i+1];
                    $params[$key] = $val;
                    $i++;
                    continue;
                } 
                if($uri_chunks[$i] == 'id') {
                    $id = (int)$uri_chunks[$i+1];
                    $i++;
                    continue;
                }
                if($uri_chunks[$i] == 'format'){
                    $format = $uri_chunks[$i+1];
                    $i++;
                    continue;
                }
            }
        }
        //  validate parameters are all present
        $param_count = count($params);
        if(isset($action)){
            switch($action){
                case 'create':
                    if($param_count != 4){
                        $this->m->response = 'Missing parameters for this action.';
                        return false;
                    }
                    break;
                case 'update':
                    if($param_count < 1 || $param_count > 4 || $id == 0){
                        $this->m->response = 'Missing parameters for this action.';
                        return false;
                    }
                    break;
                case 'delete':
                    if($id == 0){
                        $this->m->response = 'Missing id for this action.';
                        return false;
                    }
                    break;
                default:
                    break;
            } //switch
        } else {
            //  no action set
            $this->m->response = null;
            return false;
        }
        $this->action = isset($action) ? $action : null;
        $this->params = !empty($params) ? $params : null;
        $this->id = isset($id) && $id > 0 ? $id : null;
        $this->m->format = isset($format) ? $format : 'html';
        return true;
    }
    
    public function executeAction(){
        if(isset($this->action) && !empty($this->action)){
            switch($this->action){
                case 'create':
                    return $this->m->createUser($this->params);
                case 'update':
                    return $this->m->updateUser($this->params, $this->id);
                case 'delete':
                    return $this->m->deleteUser($this->id);
                case 'showall':
                    return $this->m->showAllUsers();
            }
        }
        return false;
    }

}
