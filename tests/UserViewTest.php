<?php
/**
 *  @todo setup autoloading
 *  @todo add namespace
 */
include_once '/vagrant/tests/dbTestHelper.php';
include_once '/vagrant/src/UserModel.php';
include_once '/vagrant/src/UserController.php';
include_once '/vagrant/src/UserView.php';

class UserViewTest extends PHPUnit_Framework_TestCase
{
    private $db;
    private $dbHelper;
    private $um;
    private $uc;
    private $uv;
    private $table = 'users_test';
    
    /**
     *  HTML of the default index page
     */
    private $default_index = '<!doctype html>
    <html lang="en">
       <head>
           <meta charset="utf-8">
           <meta name="description" content="Page for creating, updating, deleting, and viewing users.">
           <title> Users </title>
       </head>
       <body> Welcome to the User Management App. Available actions = | create | update | delete | showall |  </body>
    </html>';
    
    /**
     *  HTML of table rendered from the default state of the users_test table
     */    
    private $default_table = '<!doctype html>
    <html lang="en">
       <head>
           <meta charset="utf-8">
           <meta name="description" content="Page for creating, updating, deleting, and viewing users.">
           <title> Users </title>
       </head>
       <body> <table><tr><th>u_id</th><th>email</th><th>first_name</th><th>last_name</th></tr><tr><td>1</td><td>a@b.io</td><td>test</td><td>user</td></tr></table> </body>
    </html>';

    /** 
     *  Setup before each test 
     */        
    public function setUp()
    {
        $this->dbHelper = new dbTestHelper();        
        $this->db = $this->dbHelper->db;
        $this->um = new UserModel($this->db);
        $this->um->setTable($this->table);
        $this->uc = new UserController($this->um);
        $this->uv = new UserView($this->uc, $this->um);
    }

    public function testRenderHtmlReturnsValidPage()
    {
        $body = 'body';
        $html = $this->uv->renderHTML($body);
        $expected = '<!doctype html>
    <html lang="en">
       <head>
           <meta charset="utf-8">
           <meta name="description" content="'.$this->uv->description.'">
           <title> '.$this->uv->title.' </title>
       </head>
       <body> '.$body.' </body>
    </html>';

        $this->assertEquals($expected, $html);
    }

    public function testRenderJsonReturnsValidJson()
    {
        $data = 'value';
        $result = $this->uv->renderJSON($data);
        $expected = '{"data":"value"}';
        $this->assertEquals($expected, $result);
    }

    public function testRenderTableReturnsValidHTMLTable()
    {
        $array = [
                    ['c1' => 'c1-v1', 'c2' => 'c2-v1' ],
                    ['c1' => 'c1-v2', 'c2' => 'c2-v2' ],
                    ['c1' => 'c1-v3', 'c2' => 'c2-v3' ],
                ];
        $result = $this->uv->renderTable($array);
        $expected = '<table><tr><th>c1</th><th>c2</th></tr>'.
                    '<tr><td>c1-v1</td><td>c2-v1</td></tr>'.
                    '<tr><td>c1-v2</td><td>c2-v2</td></tr>'.
                    '<tr><td>c1-v3</td><td>c2-v3</td></tr></table>';
        $this->assertEquals($expected, $result);
    }

    public function testRenderWelcomeReturnsValidHtml()
    {
        $result = $this->uv->renderWelcome('html');
        $this->assertEquals($this->default_index, $result);
    }
    
    public function testRenderWelcomeReturnsValidHtmlWithFormatParameterMissing()
    {
        $result = $this->uv->renderWelcome('');
        $this->assertEquals($this->default_index, $result);
    }
    
    public function testRenderWelcomeReturnsValidJson()
    {
        $result = $this->uv->renderWelcome('json');
        $expected = '{"data":"Welcome to the User Management App. Available actions = | create | update | delete | showall | "}';
        $this->assertEquals($expected, $result);    
    }
    
    public function testRenderReturnsIndexWhenModelResponseIsNull()
    {
        $result = $this->uv->render();
        $this->assertEquals($this->default_index, $result);    
    }
    
    public function testRenderReturnsUsersTable()
    {
        $this->uc->action = 'showall';
        //  actually need to execute to get the response
        $this->uc->executeAction();
        $this->um->format = 'html';
        $result = $this->uv->render();
        $this->assertEquals($this->default_table, $result);    
    }
    
    /*
     *  @todo find a better way to test this
     */

    public function testRenderReturnsHtmlDeletedMessageAfterUserDelete()
    {
        $this->uc->id = 1;
        $this->uc->action = 'delete';
        $this->uc->executeAction();
        $result = $this->uv->render();
        $expected = '<!doctype html>
    <html lang="en">
       <head>
           <meta charset="utf-8">
           <meta name="description" content="Page for creating, updating, deleting, and viewing users.">
           <title> Users </title>
       </head>
       <body> User deleted </body>
    </html>';
        $this->assertEquals($expected, $result);    
        $this->dbHelper->resetDBState();
    }
    public function testRenderReturnsJsonDeletedMessageAfterUserDelete()
    {
        $this->uc->id = 1;
        $this->uc->action = 'delete';
        $this->uc->executeAction();
        $this->um->format = 'json';
        $result = $this->uv->render();
        $expected = '{"data":"User deleted"}';
        $this->assertEquals($expected, $result);    
        $this->dbHelper->resetDBState();
    }
}
