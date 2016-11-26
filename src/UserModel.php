<?php
/**
 * UserModel contains the business logic for user-related CRUD tasks in the app.
 * 
 * Simple, four-field user model, using a PDO database connection and MySQL syntax.           
 *              
 * @file      UserModel.php
 * @namespace app\models
 * @author    Lars A. Rehnberg
 * @version   0.0.0
 */
//namespace app\models;

/**
 * Class UserModel represents app users
 * @class   UserModel
 * @package app\models
 */

class UserModel
{
    /**
     *  @var string $table      Database table name.
     */
    protected $table = 'users';
    /*
     *  @var int    $u_id       User id number, generated upon insertion in db.
     */
    protected $u_id;
    /*
     *  @var array  $fields     List of fields required to create a new user in the db.
     *                          New fields must be added here AND in the case statmeent of validateParams()
     */
    public $fields = [ 'email', 'first_name', 'last_name', 'password' ];
    /*
     *  @var array  $public_fields  List of fields the app is allowed to display.
     */
    protected $public_fields = [ 'u_id', 'email', 'first_name', 'last_name' ];
    /*
     *  @var array  $field_count    Number of fields required to create a new user.
     */
    protected $field_count = 0;
    /*
     *  @var object  $db        PDO object connected to database.
     */
    protected $db;
    /*
     *  @var array  $errors     Array of errors generated during execution.
     */
    public $errors = [];
    /*
     *  @var string  $password_regex    Regex pattern requiring 1 letter, 1 number, and 1 special character
     */
    private $password_regex = "~^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{8,}$~";
    /*
     *  @var string  $password_key      Random hexadecimal string used in hashPassword()
     */
    private $password_key = 'C658CA8776A84';
    /*
     *  @var string  $response  Random hexadecimal string used in hashPassword()
     */    
    public $response;

    /*
     *  @param object   $db     PDO connection to database
     */        
    public function __construct($db)
    {
        $this->db = $db;
        $this->field_count = count($this->fields);
        
    }

    /*
     *  array['params']         array   Defines fields to be inserted
     *          ['email']       string  Email address must be 6 - 100 characters, and contain @
     *          ['first_name']  string  First name, non-zero length string
     *          ['last_name']   string  First name, non-zero length string
     *          ['password']    string  Password require one letter, one number, and one special character
     *  @param array    $params         See array structure above
     *  @return boolean                 true on success, false on failure
     */    
    public function createUser($params)
    {
        if(is_array($params) && $this->validateParams($params) === true){

            //hash password prior to inserting
            $params['password'] = $this->hashPassword($params['password']);

            //generate sql
            $field_names = $values = '';
            for($i = 0; $i < $this->field_count; $i++){
                $field_names .= "`" . $this->fields[$i] . "`";
                $values .= '?';
                if($i < ($this->field_count - 1)){
                    $field_names .= ', ';
                    $values .= ', ';
                }
            } 

            $sql = "INSERT INTO `".$this->table."` (" .$field_names. ") VALUES (" .$values. ");";

            //prep, bind, exec
            $stmt = $this->db->dbh->prepare($sql);
            for($i = 0; $i < $this->field_count; $i++){
                $stmt->bindValue($i + 1, $params[$this->fields[$i]]);
            }
            try {
                $inserted = $stmt->execute();
            } catch ( PDOException $e ) {
                $this->response = "New user creation failed: \n" . $this->printPDOException($e);
                return false;
            }
            $this->response = 'New user created successful';
            $this->u_id = $this->getUserIdByEmail($params['email']);
            return true;
        } else {
            //validation failed
            $this->response = $this->printErrors();
            return false;
        }
    }
    
    /*
     *  @param array    $params     Array containing one or more of the params from the fields array
     *  @param int      $id         Database id of user to update
     *  @return boolean             true on success, false on failure
     */    
    public function updateUser($params, $id)
    {
        if($this->validateParams($params) === true && is_int($id)){
            $param_count = count($params);
            $fields = array_keys($params);
            
            if(isset($params['password'])){
                $params['password'] = $this->hashPassword($params['password']);
            }
            
            //generate sql
            $values = '';
            for($i = 0; $i < $param_count; $i++){
                $values .= "`$fields[$i]` = ?";
                if($i > 0 && $i < ($param_count - 1)){
                    $values .= ', ';
                }
            }
            $sql = "UPDATE $this->table SET $values WHERE `u_id` = ?;";

            //prepare, bind, execute
            $stmt = $this->db->dbh->prepare($sql);
            foreach($fields as $k => $f){
                $stmt->bindValue($k+1, $params[$f]);
            }
            $stmt->bindValue($param_count+1, $id);
            $update_result = $stmt->execute();
            $this->response = $update_result ? 'user updated' : 'user update failed';
            return $update_result;
        } else {
            if(empty($this->errors)){ 
                $this->errors = 'ERROR: id given is not an integer.';
            }
            $this->response = $this->printErrors();
            return false;
        }
    }
    
    /*
     *  @param int      $id         Database id of user to delete
     *  @return boolean             true on success, false on failure
     */    
    public function deleteUser($id)
    {
        if(is_string($id)){
            //get user by email, just in case
            $id = $this->getUserByEmail($id);
        }
        if(is_int($id)){
            $sql = "DELETE FROM `$this->table` WHERE `u_id` = ?;";
            $stmt = $this->db->dbh->prepare($sql);
            $stmt->bindValue(1, $id, PDO::PARAM_INT);
            $deleted = $stmt->execute();
            $this->response = $deleted ? 'User deleted' : 'User delete failed';
            return $deleted;
        }
    }

    /*
     *  @param string   $email      Email address of user
     *  @return int|boolean         Returns id if found, false on failure
     */
    public function getUserIdByEmail($email)
    {
        if(is_string($email)){
            $sql = "SELECT `u_id` FROM `$this->table` WHERE `email` = ?";
            $stmt = $this->db->dbh->prepare($sql);

            $stmt->bindValue(1, $email, PDO::PARAM_STR);
            $id_result = $stmt->execute();
            if($id_result){
                $rs = $stmt->fetch($this->db->fetch_mode);
                $this->response = $rs['u_id'];
                return $rs['u_id'];
            }
            $this->response = 'User not found!';
            return false;
        }
    }

    /*
     *  @param array    $params     Array containing one or more of the params from the fields array
     *  @return boolean             true if all values are valid, false on failure
     */    
    protected function validateParams($params)
    {
        if(empty($params)){
            return false;
        }
        foreach($params as $k => $v){
            switch($k){
                case 'email': 
                    if(strlen($v) < 6 || strlen($v) > 100){
                        $this->errors[$k] = 'Email address must be 6 to 100 characters.';
                        return false;
                    }
                    if(strpos($v, '@') === false){
                        $this->errors[$k] = 'Email address must contain the @ symbol.';
                        return false;
                    }
                    break;
                case 'first_name':
                    if(strlen($v) == 0){
                        $this->errors[$k] = 'First name is required.';
                        return false;                    
                    }
                    if(!ctype_alnum($v) ){
                        $this->errors[$k] = 'First name contains non-alphanumeric characters.';
                        return false;
                    }
                    break;
                case 'last_name':
                    if(strlen($v) == 0){
                        $this->errors[$k] = 'Last name is required.';
                        return false;                    
                    }
                    if(!ctype_alnum($v) ){
                        $this->errors[$k] = 'Last name contains non-alphanumeric characters.';
                        return false;
                    }
                    break;
                case 'password':
                    if(strlen($v) == 0){
                        $this->errors[$k] = 'Password is required.';
                        return false;
                    }
                    if( !preg_match($this->password_regex, $v) ){
                        $this->errors[$k] = 'Password must be 8 characters, with at least 1 letter, 1 number, and 1 special character.';
                        return false;
                    }
                    break;
                default: break;
            }//switch
        } //fe
        return true;
        
    }
    
    /*
     *  @param string   $str    Plaintext password
     *  @return string          64-character hash of password
     */    
    private function hashPassword($str)
    {
        return hash_hmac('sha256', $str,  $this->password_key);
    }

    /*
     *  @return string          Concatenated string of all errors
     */    
    public function printErrors()
    {
        $error_resp = '';
        foreach ($this->errors as $e){
            $error_resp .= 'ERROR: '. $e .'<br>';
        }
        return $error_resp;
    }

    /*
     *  @param  PDO Exception   PDO Exception object from try-catch blocks
     *  @return string          Human-readable error string
     */    
    private function printPDOException($e)
    {
        return 'PDO EXCEPTION: ' . $e->getMessage() . ' in file ' .  $e->getFile() . ':' . $e->getLine() .'';
    }

    /*
     *  @return array|boolean   Array with all public fields for all existing users, or false on failure
     */    
    protected function getAllUsers()
    {
        $fields = '';
        $field_count = count($this->public_fields);
        for($i = 0; $i < $field_count; $i++){
            $fields .= "`".$this->public_fields[$i]."`";
            if($i < ($field_count - 1)){
                $fields .= ', ';
            }
        }
        $sql = "SELECT $fields FROM `$this->table`;";
        $stmt = $this->db->dbh->prepare($sql);

        try {
            $allUsers = $stmt->execute();
            if($allUsers){
                return $stmt->fetchAll($this->db->fetch_mode);
            }
            return false;
        } catch( PDOException $e ) {
            $this->response = $this->printPDOException($e);
            return false;
        }
    }
    
    /*
     *  @return array|string   Array with all public fields for all existing users, or error message on failure
     */    
    public function showAllUsers()
    {
        $users = $this->getAllUsers();
        $this->response = $users ? $users : 'ERROR: Unable to show all users.';
    }
}
