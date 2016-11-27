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
     *  @var object $m  UserModel object
     */
    private $m;
    /**
     *  @var object $c  UserController object
     */
    private $c;
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
     *  @param object $c  UserController object
     *  @param object $m  UserModel object
     */
    public function __construct($c, $m) 
    {
        $this->c = $c;
        $this->m = $m;
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
     *  @param mixed $data  String or array
     *  @return string      JSON-encoded string
     */
    public function renderJSON($data)
    {
        return json_encode( ['data' => $data] );
    }
    
    /**
     *  @param array $array     0-indexed array of arrays with key-value pairs
     *  @return string          HTML table
     */
    public function renderTable($array)
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
     *  @param string $format  'html' or 'json'
     *  @return string
     */
    public function renderIndex($format)
    {
        $str = 'Welcome to the User Management App. Available actions = | ';
        foreach($this->c->actions as $a){
            $str .= $a.' | ';
        }
        switch($format){
            case 'json':
                return $this->renderJSON($str);
                break;
            case 'html':
                return $this->renderHTML($str);
                break;
            default:
                return $this->renderHTML($str);
                break;
        }
    }
    
    /**
     *  @param object $c  UserController object
     *  @param object $m  UserModel object
     *  @return string
     */
    public function render()
    {
        $format = strtolower($this->m->format);
        //  if there is no response from the model, display the index
        if($this->m->response == null){
            return $this->renderIndex($format);
        }
        //  special case for displaying HTML table of users
        if($this->c->action == 'showall' && $format == 'html'){
            $table = $this->renderTable( $this->m->response );
            return $this->renderHTML( $table ); 
        }
        //  default cases
        switch($format){
            case 'html':
                return $this->renderHTML( $this->m->response );
            case 'json':
                return $this->renderJSON( $this->m->response );
            default: 
                break;
        }
    }
}
