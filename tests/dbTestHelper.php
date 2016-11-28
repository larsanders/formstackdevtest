<?php
/*
 *  I did not find the DBUnit extension in the vagrant install, 
 *  so I rolled my own helper class.
 */
 
// namespace tests;

require_once '/vagrant/src/DB.php';

// use database\DB;

class dbTestHelper
{

    public $db;
    public $table = 'users_test';
    public $initial_db_state;
    public $current_db_state;
    public $seed_data =  ['u_id' => 1,
                        'email' => 'a@b.io',
                        'first_name' => 'test', 
                        'last_name' => 'user',   
                        'password' => 'ca6d8d3efe5ad313b5e0c6d4dab7f3cd3a1ad03b1eaf829cc6bd6b91106cf1e5'
                        ];
    public $db_ready = false;
                      
    /**
     *  Generates a database connection
     */        
    public function __construct($other_config = [])
    {
        include '/vagrant/config/settings.php';
        $settings = !empty($other_config) ? $other_config : $settings;
        $this->db = new DB($settings);
        $this->getInitialDBState();
        $this->testInitialDBState();
    }

    /**
     *  Retrieves seed data from db
     */
    public function getInitialDBState()
    {
        $stmt = $this->db->dbh->prepare("SELECT * FROM `$this->table`;");
        $stmt->execute();
        $this->initial_db_state = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     *  Retrieves current data from db
     */
    public function getCurrentDBState()
    {
        $stmt = $this->db->dbh->prepare("SELECT * FROM `$this->table`;");
        $stmt->execute();
        $this->current_db_state = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     *  Resets database to inital state
     */
    public function resetDBState()
    {
        //  delete all rows
        $sql = "DELETE FROM `$this->table` WHERE `u_id` > 0;";
        $stmt = $this->db->dbh->prepare($sql);
        $stmt->execute();

        //  re-insert seed data
        $fields = array_keys($this->seed_data);
        $field_count = count($fields);
        $field_names = $values = '';
        for ($i = 0; $i < $field_count; $i++) {
            $field_names .= "`" . $fields[$i] . "`";
            $value = $this->seed_data[$fields[$i]];
            if (is_string($value)) {
                $values .= "'".$value."'";
            } else {
                $values .= $value;
            }
            if ($i < ($field_count - 1)) {
                $field_names .= ', ';
                $values .= ', ';
            }
        } 

        $sql = "INSERT INTO `$this->table` (" .$field_names. ") VALUES (" .$values. ");";
        $stmt = $this->db->dbh->prepare($sql);
        $stmt->execute();
    }
    
    /**
     *  Ensures correct data at instantiation
     */
    public function testInitialDBState()
    {
        if ($this->seed_data === $this->initial_db_state) {
            $this->db_ready = true;
        } else {
            $this->resetDBState();
            $this->db_ready = true;
        }
    }
    
    /**
     *  Returns a single record
     *  
     *  @param string $query    A valid MySQL query
     */
    public function fetchOne($query)
    {
        $stmt = $this->db->dbh->prepare($query);
        if ($stmt->execute()){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    }
}