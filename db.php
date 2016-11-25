<?php

/************************************\
    BASIC PDO/MYSQL DATABASE PIPE
\************************************/

class DB {
    private $config = [ 'db_server' => '',
                        'db_name' => '',
                        'db_user' => '',
                        'db_pass' => '',
                        'db_port' => 0,
                        'db_charset' => '',
                        ];
    private $db_server;
    private $db_name;
    private $db_user;
    private $db_pass;
    private $db_port;
    private $db_charset;
    private $db_dsn;
    private $db_table;
    public $error;
    public $dbh;
    /*
     * The default fetch mode of the connection.
     * @var int
     */
    public $fetch_mode = PDO::FETCH_ASSOC;

    
    /*
        Requires array of database connection parameters
    */
    public function __construct($config = []){
        $this->db_server = $config['db_server'] ? $config['db_server'] : '127.0.0.1';
        $this->db_name = $config['db_name'] ? $config['db_name'] : '';
        $this->db_user = $config['db_user'] ? $config['db_user'] : '';
        $this->db_pass = $config['db_pass'] ? $config['db_pass'] : '';
        $this->db_port = $config['db_port'] ? $config['db_port'] : 3306;
        $this->db_charset = isset($config['db_charset']) ? $config['db_charset'] : 'UTF8';
        
        $this->setDSN();
        $this->connectDBH();
    }
    
    /*
        Setter for DSN string
    */
    private function setDSN(){
        $this->db_dsn = "mysql:host=$this->db_server;dbname=$this->db_name;port=$this->db_port;charset=$this->db_charset";
    }

    /*
        Connects database handler, prints error message on failure
    */
    private function connectDBH(){
        try {
            $dbh = new PDO($this->db_dsn, $this->db_user, $this->db_pass);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->dbh = $dbh;
        } catch(PDOException $e) {
            $this->printError($e);
        }    
    }
    
    /*
        Prints error message to html or json
    */
    private function printError($e){
        echo '<h4>PDO EXCEPTION</h4>' . $e->getMessage() . ' in file ' .  $e->getFile() . ':' . $e->getLine() .'<br>';
    }
    
    public function setTable($table){
        if(is_string($table)){
            $this->table = $table;
        }
    }

    public function getOne($query){
        $res = $this->dbh->prepare($query);
        
    }
    
    public function getAll(){
    
    }

}
