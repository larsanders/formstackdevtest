<?php
/**
 *  @todo setup autoloading
 *  @todo add namespace
 */
 
// include_once '/vagrant/src/database/DB.php';
include_once '/vagrant/src/DB.php';

// use database\DB;

class DBTest extends PHPUnit_Framework_TestCase
{
    private $db;
    
    public function setUp(){
        include '/vagrant/config/settings.php';
        $this->db = new DB($settings);
    }
    
    public function testStandardConfig(){
        $this->assertNotNull($this->db->dbh);
    }
    
    public function testBadConfig(){
        // $this->setExpectedException(PDOException::class);
        $settings = [];
        $this->db = new DB($settings);
        $this->assertNotNull($this->db->error);
    }
}
