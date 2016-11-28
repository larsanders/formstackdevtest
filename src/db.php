<?php

/**
 * Basic PDO/MySQL database object
 * 
 * @class     DB
 * @file      DB.php
 * @namespace src\db
 * @author    Lars A. Rehnberg
 * @version   0.0.1
 */

// namespace database;

// use \PDO;

class DB {
    public $error;
    public $dbh;
    public $fetch_mode = PDO::FETCH_ASSOC;
    private $db_server;
    private $db_name;
    private $db_user;
    private $db_pass;
    private $db_port;
    private $db_charset;
    private $db_dsn;
    
    /**
     *  @param array $config    Array of database connection parameters
     */
    public function __construct($config = []){
        $this->db_server = isset($config['db_server']) ? $config['db_server'] : '127.0.0.1';
        $this->db_name = isset($config['db_name']) ? $config['db_name'] : '';
        $this->db_user = isset($config['db_user']) ? $config['db_user'] : '';
        $this->db_pass = isset($config['db_pass']) ? $config['db_pass'] : '';
        $this->db_port = isset($config['db_port']) ? $config['db_port'] : 3306;
        $this->db_charset = isset($config['db_charset']) ? $config['db_charset'] : 'UTF8';
        $this->setDSN();
        $this->connectDBH();
    }
    
    /**
     *  Setter for DSN string
     *  @return string
     */
    private function setDSN(){
        $this->db_dsn = "mysql:host=$this->db_server;dbname=$this->db_name;port=$this->db_port;charset=$this->db_charset";
    }

    /**
     *  Sets database handler, or error message on failure
     */
    private function connectDBH(){
        try {
            $dbh = new \PDO($this->db_dsn, $this->db_user, $this->db_pass);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->dbh = $dbh;
        } catch (PDOException $e) {
            $this->error = $this->printPDOException($e);
        }    
    }
    
    /**
     *  @param PDO Exception $e     Exception object
     *  @return string
     */
    private function printPDOException($e){
        return "PDO EXCEPTION: " . $e->getMessage() . " in file " . $e->getFile().":".$e->getLine()." \n";
    }
}
