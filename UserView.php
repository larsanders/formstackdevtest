<?php
/**
 * UserView render output in HTML or JSON.
 *              
 * @file      UserView.php
 * @namespace app\views
 * @author    Lars A. Rehnberg
 * @version   0.0.1
 */
//namespace app\models;

/**
 * Class UserView returns HTML or JSON
 * @class   UserView
 * @package app\models
 */

class UserView
{
    /**
     *  @var object $model  UserModel object
     */
    private $model;
    /**
     *  @var object $controller  UserController object
     */
    private $controller;
    /**
     *  @var string $format  Accepts 'html' or 'json'
     */
    public $format;
    /**
     *  @var string $title  Becomes the title of the html page
     */
    public $title = 'Users';
    /**
     *  @var string $description  Becomes the description of the html page
     */
    public $description = 'Page for creating, updating, deleting, and viewing users.';


    /**
     *  @param object $controller  UserController object
     *  @param object $model  UserModel object
     */
    public function __construct($controller, $model) 
    {
        $this->controller = $controller;
        $this->model = $model;
    }

    /**
     *  @param string $body     UserController object
     *  @return string          HTML page
     */
    public function renderHTML($body)
    {
        $html = <<<EOT
<!doctype html>
    <html lang="en">
       <head>
           <meta charset="utf-8">
           <meta name="description" content="$this->description">
           <title> $this->title </title>
       </head>
       <body> $body </body>
    </html>
EOT;

        return $html;
    }

    /**
     *  @param array $array     0-indexed array of arrays with key-value pairs
     *  @return string          HTML table
     */
    protected function renderTable($array)
    {
        $html = '<table>';
        $keys = array_keys($array[0]);
        $html .= '<tr>';
        foreach($keys as $k){
            $html .= '<th>'.$k.'</th>';
        }
        $html .= '</tr>';
        foreach($array as $k => $a){
            $html .= '<tr>';
            foreach($a as $key => $v){
                $html .= '<td>'.$v.'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }
    
    /**
     *  @param object $controller  UserController object
     *  @param object $model  UserModel object
     *  @return string
     */
    public function render($format = 'html')
    {
        switch(strtolower($format)){
            case 'html':
                return $this->renderHTML( $this->model->response );
            case 'table':
                $table = $this->renderTable( $this->model->response );
                return $this->renderHTML( $table );
            case 'json':
                return json_encode( ['resp' => $this->model->response] );
            default: break;
        }
    }
}
