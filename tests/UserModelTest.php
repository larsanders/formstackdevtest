<?php
/**
 *  @todo setup autoloading
 *  @todo add namespace
 */
include '/vagrant/src/UserModel.php';
include '/vagrant/src/db.php';


class UserModelTest extends PHPUnit_Framework_TestCase
{
    private $db;
    private $um;
    private $table = 'users_test';
    public $initialDBState;
    public $seed_data =  ['u_id' => 1,
                        'email' => 'a@b.io',
                        'first_name' => 'test', 
                        'last_name' => 'user',   
                        'password' => 'ca6d8d3efe5ad313b5e0c6d4dab7f3cd3a1ad03b1eaf829cc6bd6b91106cf1e5'
                        ];

    public function setUp()
    {
        $this->db = $this->getDBConnection();
        $this->um = new UserModel($this->db);
        $this->um->setTable($this->table);
        $this->getInitialDBState();
    }
    
    public function tearDown()
    {
        $this->db = null;
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
     *  Generates a database connection
     */        
    public function getDBConnection(){
        include '/vagrant/config/settings.php';
        $db = new DB($settings);
        return $db;    
    }

    /**
     *  Retrieves seed data from db
     */
    public function getInitialDBState()
    {
        $stmt = $this->db->dbh->prepare("SELECT * FROM $this->table;");
        $stmt->execute();
        $actual = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->initialDBState = $actual;    
    }
    
    /**
     *  Retrieves current data from db
     */
    public function getCurrentDBState()
    {
        $stmt = $this->db->dbh->prepare("SELECT * FROM $this->table;");
        $stmt->execute();
        $current = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->currentDBState = $current;        
    }

    /**
     *  Resets database to inital state
     */
    public function resetDBState(){
        // delete all rows
        $sql = "DELETE FROM $this->table WHERE `u_id` > 0;";
        $stmt = $this->db->dbh->prepare($sql);
        $stmt->execute();

        $fields = array_keys($this->seed_data);
        $field_count = count($fields);
        $field_names = $values = '';
        for($i = 0; $i < $field_count; $i++){
            $field_names .= "`" . $fields[$i] . "`";
            $value = $this->seed_data[$fields[$i]];
            if(is_string($value)){
                $values .= "'".$value."'";
            } else {
                $values .= $value;
            }
            if($i < ($field_count - 1)){
                $field_names .= ', ';
                $values .= ', ';
            }
        } 

        $sql = "INSERT INTO `$this->table` (" .$field_names. ") VALUES (" .$values. ");";
        $stmt = $this->db->dbh->prepare($sql);
        $stmt->execute();
    }
    
    public function testInitialStateOfDatabase()
    {
        $this->assertEquals($this->seed_data, $this->initialDBState);
    }

    public function testCreateUserReturnsFalseWithEmptyParamsArray()
    {
        $params = [];
        $this->assertFalse($this->um->createUser($params));
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
                [ ['email' => 'a',           // Email too short (6-100 chars)
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
     *  Should actually create a new user in the `users_test` table
     *  Should also delete the user upon success
     */
    public function testCreateUserReturnsTrueWithValidParamsArray()
    {
        $params = [ 'email' => 'a@c.io',
                  'first_name' => 'bob', 
                  'last_name' => 'jens',   
                  'password' => 'password1*'
                ];
        $r = $this->um->createUser($params);
        if($r){
            $p = self::getProperty('u_id');
            $this->um->deleteUser($p->getValue($this->um));
        }
        $this->assertTrue($r);
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
        //reset
        $this->resetDBState();
    }

    public function testUpdateUserChangesEmailValueInDatabase()
    {
        //  update data
        $params = ['email' => 'changed@this.app'];
        $id = 1;
        $this->um->updateUser($params, $id);
        //  retrieve updated data
        $stmt = $this->db->dbh->prepare("SELECT `email` FROM `$this->table` WHERE `u_id` = 1;");
        $stmt->execute();
        $updated = $stmt->fetch(PDO::FETCH_ASSOC);
        $this->assertEquals($params['email'], $updated['email']);
        //  reset data
        $this->resetDBState();
    }    

    public function testUpdateUserChangesPasswordValueInDatabase()
    {
        //  update data
        $params = ['password' => 'marvelous1!'];
        $id = 1;
        $this->um->updateUser($params, $id);
        //  retrieve updated data
        $stmt = $this->db->dbh->prepare("SELECT `password` FROM `$this->table` WHERE `u_id` = 1;");
        $stmt->execute();
        $updated = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $m = self::getMethod('hashPassword');
        $expected = $m->invokeArgs($this->um, [$params['password']]);
        $this->assertEquals($expected, $updated['password']);
        //  reset data
        $this->resetDBState();
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
        $this->resetDBState();
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
        $this->assertEquals('users_test', $this->um->getTable());
    }
}
