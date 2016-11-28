<?php
/**
 * Class UserView renders HTML or JSON strings
 *              
 * @class       UserView
 * @file        UserView.php
 * @namespace   app\views
 * @author      Lars A. Rehnberg
 * @version     0.0.1
 */
 
// namespace views;

class UserView
{
    /**
     *  @var object $m      UserModel object
     */
    private $m;
    /**
     *  @var object $c      UserController object
     */
    private $c;
    /**
     *  @var string $title      Title element of the html page
     */
    public $title = 'Users';
    /**
     *  @var string $description    Description of the html page
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
     *  Outputs simmple HTML 5 index  
     *
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
     *  Converts strings or array to JSON
     *
     *  @param mixed $data  String or array
     *  @return string      JSON-encoded string
     */
    public function renderJSON($data)
    {
        return json_encode( ['data' => $data] );
    }
    
    /**
     *  Converts multidimensional array into HTML an table
     *  Each sub-array must identical associative keys
     *
     *  @param array $array     0-indexed array of arrays with key-value pairs
     *  @return string          HTML table
     */
    public function renderTable($array)
    {
        $keys = array_keys($array[0]);

        $html = '<table>';
        $html .= '<tr>';
        foreach ($keys as $k) {
            $html .= '<th>'.$k.'</th>';
        }
        $html .= '</tr>';
        foreach ($array as $k => $a) {
            $html .= '<tr>';
            foreach ($a as $key => $v) {
                $html .= '<td>'.$v.'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }

    /**
     *  Renders a welcome page
     *
     *  @param string $format  Allows override of model's default format
     *  @return string
     */
    public function renderWelcome($format = '')
    {
        if ($format != '' && is_string($format)) {
            $this->m->format = $format;
        }
        $str = 'Welcome to the User Management App. Available actions = | ';
        foreach ($this->c->actions as $a) {
            $str .= $a.' | ';
        }
        switch ($this->m->format) {
            case 'json':
                return $this->renderJSON($str);
            case 'html':
                return $this->renderHTML($str);
        }
    }
    
    /**
     *  Main function call to output the View 
     *
     *  @return string
     *  @todo refactor to avoid special case
     */
    public function render()
    {
        
        //  if there is no response from the model, display the index
        if ($this->m->response == null) {
            return $this->renderWelcome();
        }
        
        //  special case for displaying HTML table of users
        if ($this->c->action == 'showall' && $this->m->format == 'html') {
            $table = $this->renderTable($this->m->response);
            return $this->renderHTML($table); 
        }
        
        //  standard cases for non-null responses
        switch ($this->m->format) {
            case 'html':
                return $this->renderHTML($this->m->response);
            case 'json':
                return $this->renderJSON($this->m->response);
        }
    }
}
