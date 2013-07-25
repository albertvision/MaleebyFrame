<?php

namespace Maleeby\Libraries;

class DB {

    private static $_instance = null;
    private static $_config = array();
    
    /**
     * Connection array
     * @var array 
     */
    private static $_con = null;
    
    /**
     * Current prepare statement
     * @var type 
     */
    private $stmt;
    
    /**
     * Query
     * @var string 
     */
    private $query;
    
    /**
     * Query's parameters
     * @var array 
     */
    private $params = array();


    public static function load() {
        if(self::$_instance == null) {
            self::$_instance = new \Maleeby\Libraries\DB();
        }
        return self::$_instance;
    }
    
    public static function connect() {
        $db = self::load();
        if(self::$_con == null) {
            self::$_config = \Maleeby\Core::load()->getConfig()->database;
            $_conData = self::$_config;
            self::$_con = new \PDO((isset($_conData['driver']) ? $_conData['driver'] : 'mysql').':host='.$_conData['dbhost'].';dbname='.$_conData['dbname'], $_conData['dbuser'], $_conData['dbpass'], (isset($_conData['pdo_options']) ? $_conData['pdo_options'] : array()));
        }
        return $db;
    }

    
    /**
     * Create prepare statement
     * @param string $query Query
     * @param array $params Query's parameters
     * @param array $pdoOptions PDO options
     * @return \Maleeby\Database
     */
    public static function prepare($query, $params = array(), $pdoOptions = array()) {
        $db = self::connect();
        $db->stmt = self::$_con->prepare($query, $params);
        $db->params = $params;
        $db->query = $query;
        
        return $db;
    }
    
    /**
     * Execute query
     * @param array $params Query's parameters if they are not defined in prepare() method
     * @return \Maleeby\Database
     */
    public static function execute($params = array()) {
        $db = self::connect();
        if (count($params)) {
            $db->params = $params;
        }
        $db->stmt->execute($db->params);
        if(self::getError() !== FALSE) {
            if(\Maleeby\Core::load()->getConfig()->main['debug'] == TRUE) {
                throw new \Exception(self::getError(), 500);
            } else {
                \Maleeby\ErrorHandling::logError(array(__DIR__.'/Database.php', 500, '?', self::getError()));
                return false;
            }
        }
        return $db;
    }
    
    /**
     * Create auto executing query
     * @param string $query Query
     * @param array $params Query's parameters
     * @param array $pdoOptions PDO options
     * @return \Maleeby\Database
     */
    public static function query($query, $params = array(), $pdoOptions = array()) {
        return self::prepare($query, $params, $pdoOptions)->execute();
    }
    
    /**
     * Get SQL Error Code
     * @return string|bool
     */
    public static function getError() {
        $db = self::connect();
        $err = $db->stmt->errorCode();
        $errInfo = $db->stmt->errorInfo();
        if($err != '00000') {
            return $errInfo[2];
        }
        return false;
    }
    
    /**
     * Fetching of query
     * @return array 
     */
    public static function fetchAssoc() {
        $db = self::connect();
        return $db->stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get a row
     */
    public static function fetchRowAssoc() {
        $db = self::connect();
        return $db->stmt->fetch(\PDO::FETCH_ASSOC);
    }
 
    /**
     * Fetching of column
     * @param string $column Column name
     * @return array
     */
    public static function fetchColumn($column) {
        $db = self::connect();
        return $db->stmt->fetchAll(\PDO::FETCH_COLUMN, $column);
    }

    /**
     * Get ID of the last inserted row
     * @return int
     */
    public static function lastID() {
        $con = self::$_con;
        return $con->lastInsertId();
    }

    /**
     * Get count of the affected rows
     * @return int
     */
    public function numRows() {
        $db = self::connect();
        return $db->stmt->rowCount();
    }

    /**
     * Get prepare statement's property. FOR ADVANCED USERS!!
     * @return object
     */
    public static function getSTMT() {
        $db = self::connect();
        return $db->stmt;
    }

}

?>