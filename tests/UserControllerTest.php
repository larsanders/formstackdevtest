<?php
/**
 *  @todo setup autoloading
 *  @todo add namespace
 */
include_once '/vagrant/tests/dbTestHelper.php';
include_once '/vagrant/src/UserModel.php';
include_once '/vagrant/src/UserController.php';


class UserControllerTest extends PHPUnit_Framework_TestCase
{
    private $db;
    private $dbHelper;
    private $um;
    private $uc;
    private $table = 'users_test';

    public function setUp()
    {
        $this->dbHelper = new dbTestHelper();        
        $this->db = $this->dbHelper->db;
        $this->um = new UserModel($this->db);
        $this->um->setTable($this->table);
        $this->uc = new UserController($this->um);
    }

   /**
     * @param string $uri Invalid URI to test
     *
     * @dataProvider providerTestParseRouteReturnsFalseWithInvalidUri
     */
    public function testParseRouteReturnsFalseWithInvalidUri($uri)
    {
        $this->assertFalse($this->uc->parseRoute($uri));        
    }

    public function providerTestParseRouteReturnsFalseWithInvalidUri()
    {
        return [
                [ '/index.php?actio=create' ],                  // typo
                [ '/creat/email/' ],                            // typo
                [ '/index.php?action=create&email=a@b.io' ],    // missing parameters
                [ '/create/email/a@b.io' ],                     // missing parameters
                [ '/update/email/a@b.io' ],                     // missing parameters
                [ '/index.php?action=delete&id=' ],             // missing id
                [ '/delete/id/' ],                              // missing id
                [ '/' ]                                         // no action
            ];
    }

   /**
     * @param string $uri Valid URI to test
     *
     * @dataProvider providerTestParseRouteReturnsTrueWithValidUri
     */
    public function testParseRouteReturnsTrueWithValidUri($uri)
    {
        $this->assertTrue($this->uc->parseRoute($uri));        
        $this->dbHelper->resetDBState(); 
    }

    public function providerTestParseRouteReturnsTrueWithValidUri()
    {
        return [
                [ '/create/email/a@b.io/first_name/foo/last_name/bar/password/8c88*SW1' ],
                [ '/index.php?action=create&email=a@b.io&first_name=foo&last_name=bar&password=8c88*SW1' ],
                [ '/update/id/8/first_name/jerry' ],
                [ '/index.php?action=update&id=8&first_name=jerry' ],
                [ '/delete/id/8' ],
                [ '/index.php?action=delete&id=8' ],
                [ '/showall/format/json' ],
                [ '/index.php?action=showall&format=json' ],
                [ '/showall' ],
            ];
    }

    public function testExecuteActionReturnsIntWithCreateUserAction()
    {
        $this->uc->action = 'create';
        $params = [ 'email' => 'new@user.io',
                  'first_name' => 'newTest', 
                  'last_name' => 'user',   
                  'password' => 'password1*'
                ];
        $this->uc->params = $params;
        $this->assertTrue($this->uc->executeAction());
        $this->assertInternalType('int', $this->um->response);
        $this->dbHelper->resetDBState(); 
    }

    public function testExecuteActionReturnsTrueWithUpdateAction()
    {
        $this->uc->action = 'update';
        $this->uc->id = 1;
        $this->uc->params = ['email' => 'new@email.com'];
        $this->assertTrue($this->uc->executeAction());
        $this->dbHelper->resetDBState();
    }

    public function testExecuteActionReturnsTrueWithDeleteAction()
    {
        $this->uc->action = 'delete';
        $this->uc->id = 1;
        $this->assertTrue($this->uc->executeAction());
        $this->dbHelper->resetDBState();
    }

    public function testExecuteActionReturnsArrayWithShowAllAction()
    {
        $this->uc->action = 'showall';
        $this->assertInternalType('array', $this->uc->executeAction());
    }

    public function testExecuteActionReturnsFalseWithNoAction()
    {
        $this->uc->action = null;
        $this->assertFalse($this->uc->executeAction());
    }
}
