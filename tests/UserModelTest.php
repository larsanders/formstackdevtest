<?php
/**
 *  @todo setup autoloading
 *  @todo add namespace
 */
include_once '/vagrant/tests/dbTestHelper.php';
include_once '/vagrant/src/UserModel.php';

class UserModelTest extends PHPUnit_Framework_TestCase
{
    private $db;
    private $dbHelper;
    private $um;
    private $table = 'users_test';

    /** Setup before each test */        
    public function setUp()
    {
        $this->dbHelper = new dbTestHelper();        
        $this->db = $this->dbHelper->db;
        $this->um = new UserModel($this->db);
        $this->um->setTable($this->table);
    }

    /**
     *  Allows access to protected and private methods
     *
     *  @param $name    The name of the method to access
     */        
    public static function getMethod($name) 
    {
        $class = new ReflectionClass('UserModel');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     *  Allows access to protected and private properties
     *
     *  @param $name    The name of the property to access
     */        
    public static function getProperty($name) 
    {
        $class = new ReflectionClass('UserModel');
        $prop = $class->getProperty($name);
        $prop->setAccessible(true);
        return $prop;
    }
    
   /**
     * @param array $params Data used to create new user
     *
     * @dataProvider providerTestCreateUserReturnsFalseWithInvalidParamsArray
     */
    public function testCreateUserReturnsFalseWithInvalidParamsArray($params)
    {
        $r = $this->um->createUser($params);
        $this->assertFalse($r);        
    }
    
    public function providerTestCreateUserReturnsFalseWithInvalidParamsArray()
    {
        return [
                [ [] ],                     // Empty array
                [ ['email' => 'a',          // Email too short (6-100 chars)
                  'first_name' => 'a',
                  'last_name' => 'a',
                  'password' => 'a' ]
                ],
                [ ['email' => 'abcd.io',
                  'first_name' => '',       // Email missing @ symbol
                  'last_name' => '',
                  'password' => '']
                ],
                [ ['email' => 'a@b.io',
                  'first_name' => '',       // No first name
                  'last_name' => '',
                  'password' => '']
                ],
                [ ['email' => 'a@b.io',
                  'first_name' => 'b*b',    // First name contains special chars
                  'last_name' => '',
                  'password' => '']
                ],
                [ ['email' => 'a@b.io',
                  'first_name' => 'bob',   
                  'last_name' => '',        // No last name
                  'password' => '']
                ],
                [ ['email' => 'a@b.io',
                  'first_name' => 'bob', 
                  'last_name' => 'j*ns',    // Last name contains special chars
                  'password' => '']
                ],
                [ ['email' => 'a@b.io',
                  'first_name' => 'bob', 
                  'last_name' => 'jens',   
                  'password' => '']         // No password
                ],
                [ ['email' => 'a@b.io',
                  'first_name' => 'bob', 
                  'last_name' => 'jens',   
                  'password' => 'letmein']  // Password too short (8)
                ],
                [ ['email' => 'a@b.io',
                  'first_name' => 'bob', 
                  'last_name' => 'jens',   
                  'password' => 'password'] // Password contains no numbers
                ],
                [ ['email' => 'a@b.io',
                  'first_name' => 'bob', 
                  'last_name' => 'jens',   
                  'password' => 'password1']// Password contains no special chars
                ]
            ];
    }


   /**
     *
     */
    public function testCreateUserReturnsFalseWhenEmailAlreadyExists()
    {
        $params = [ 'email' => 'a@b.io',
                  'first_name' => 'bob', 
                  'last_name' => 'jens',   
                  'password' => 'password1*'
                ];
        $r = $this->um->createUser($params);
        $this->assertFalse($r);
        $this->dbHelper->resetDBState();
    }

   /**
     *  Should actually create a new user in the `users_test` table
     *  Should also delete the user upon success
     */
    public function testCreateUserReturnsTrueWithValidParamsArray()
    {
        $params = [ 'email' => 'a@f.io',
                  'first_name' => 'bob', 
                  'last_name' => 'jens',   
                  'password' => 'password1*'
                ];
        $r = $this->um->createUser($params);
        $this->assertTrue($r);
        $this->dbHelper->resetDBState();
    }

    public function testValidateParamsWithBadEmailAddress()
    {
        $m = self::getMethod('validateParams');
        $params = ['email' => 'a'];
        $this->assertFalse($m->invokeArgs($this->um, [$params]));
    }

   /**
     * @param array $params Data used to create or update users
     *
     * @dataProvider providerTestValidateParamsReturnsTrueWithValidParamsArray
     */
    public function testValidateParamsReturnsTrueWithValidParamsArray($params)
    {
        $m = self::getMethod('validateParams');
        $this->assertTrue($m->invokeArgs($this->um, [$params]));
    }

    public function providerTestValidateParamsReturnsTrueWithValidParamsArray()
    {
        return [
                [ ['email' => 'a@a.io'] ],
                [ ['first_name' => 'frank'] ], 
                [ ['last_name' => 'gehry'] ],
                [ ['password' => 'inconci3vabl3!'] ],
            ];
    }

    public function testValidateIdReturnsFalseWithInvalidId()
    {
        $id = 'not an id';
        $this->assertFalse($this->um->validateID($id));
    }
    
    public function testUpdateUserReturnsFalseWithInvalidParamsArray()
    {
        $params = [];
        $id = 0;
        $this->assertFalse($this->um->updateUser($params, $id));    
    }

    public function testUpdateUserReturnsFalseWithInvalidId()
    {
        $params = ['email' => 'changed@this.app'];
        $id = 0;
        $this->assertFalse($this->um->updateUser($params, $id));    
    }
    
    public function testUpdateUserReturnsTrueWithValidParamsArrayAndId()
    {
        $params = ['email' => 'changed@this.app'];
        $id = 1;
        $this->assertTrue($this->um->updateUser($params, $id));
        $this->dbHelper->resetDBState();
    }

    public function testUpdateUserReturnsTrueWithMultipleValidParamsAndId()
    {
        $params = ['email' => 'changed@this.app',
                   'first_name' => 'newname' ];
        $id = 1;
        $this->assertTrue($this->um->updateUser($params, $id));
        $this->dbHelper->resetDBState();
    }

    public function testUpdateUserChangesEmailValueInDatabase()
    {
        //  update data
        $params = ['email' => 'changed@this.app'];
        $id = 1;
        $this->um->updateUser($params, $id);
        //  retrieve updated data
        $sql = "SELECT `email` FROM `$this->table` WHERE `u_id` = 1;";
        $updated = $this->dbHelper->fetchOne($sql);
        $this->assertEquals($params['email'], $updated['email']);
        //  reset data
        $this->dbHelper->resetDBState();
    }    

    public function testUpdateUserChangesPasswordValueInDatabase()
    {
        //  update data
        $params = ['password' => 'marvelous1!'];
        $id = 1;
        $this->um->updateUser($params, $id);
        //  retrieve updated data
        $sql = "SELECT `password` FROM `$this->table` WHERE `u_id` = 1;";
        $updated = $this->dbHelper->fetchOne($sql);
        
        $m = self::getMethod('hashPassword');
        $expected = $m->invokeArgs($this->um, [$params['password']]);
        $this->assertEquals($expected, $updated['password']);
        //  reset data
        $this->dbHelper->resetDBState();
    }    

    public function testDeleteUserReturnsFalseWithInvalidId()
    {
        $id = 0;
        $this->assertFalse($this->um->deleteUser($id));    
    }
    
    public function testDeleteUserReturnsTrueWithValidId()
    {
        $id = 1;
        $this->assertTrue($this->um->deleteUser($id));    
        $this->dbHelper->resetDBState();
    }
    
    public function testGetUserIdByEmailReturnsFalseWithInvalidEmailType()
    {
        $email = 1; 
        $this->assertFalse($this->um->getUserIdByEmail($email));
    }

    public function testGetUserIdByEmailReturnsFalseWithEmailNotInDatabase()
    {
        $email = 'thereisnowaythisemailisinthere@noway.com';
        $this->assertFalse($this->um->getUserIdByEmail($email));
    }
    
    public function testShowAllUsers()
    {
        $users = $this->um->showAllUsers();
        $this->assertInternalType('array', $users);
    }
    
    public function testGetTableReturnsCorrectValue()
    {
        $this->assertEquals($this->table, $this->um->getTable());
    }

    public function showAllUsersThrowsExceptionWhenPublicFieldsCorrupted()
    {
        $this->um->public_fields = [];
        $this->assertFalse($this->um->showAllUsers());
    }
}
