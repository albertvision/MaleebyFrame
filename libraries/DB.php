<?php

namespace Maleeby\Libraries;

/**
 * Database connection class
 *
 * @author Yasen Georgiev <avbincco@gmail.com>
 * @link http://framework.maleeby.ygeorgiev.com/
 * @copyright Copyright &copy; 2013 Yasen Georgiev
 * @license http://framework.maleeby.ygeorgiev.com/#license
 * @package Libraries
 */
class DB {

    /**
     * Instance of this class
     * @var object|null
     * @static
     */
    private static $_instance = null;
    
    /**
     * DB configuration
     * @access private
     * @static
     * @var array
     */
    private static $_config = array();
    
    /**
     * Connection array
     * @access private
     * @static
     * @var array 
     */
    private static $_con = null;
    
    /**
     * Current prepare statement
     * @access private
     * @var type 
     */
    private $stmt;
    
    /**
     * Query
     * @access private
     * @var string 
     */
    private $query;
    
    /**
     * Query's parameters
     * @access private
     * @var array 
     */
    private $params = array();
    
    /**
     * Current connection
     * @var string
     */
    private static $_currentCon = 'default';
    
    /**
     * Loaded connection
     * @var array
     */
    private static $_connections = array();

    /**
     * Get instantion of this class
     * @static
     * @return type
     */
    public static function load() {
        if(self::$_instance == null) {
            self::$_instance = new \Maleeby\Libraries\DB();
        }
        return self::$_instance;
    }
    
    /**
     * DB Connection
     * @param string|object $con Connection name or PDO object
     * @return object DB Class
     * @throws \Exception
     */
    public static function connect($con = null) {
        $db = self::load();
        
        self::$_config = \Maleeby\Core::load()->getConfig()->database;
        
        if($con == null) { // Set default connection
            $con = self::$_currentCon; 
        } else { // Set current connection
            self::$_currentCon = $con;
        }
        
        if($con instanceof \PDO) {
            self::$_con = $con;
        } elseif($con != null) {
            $_conData = self::$_config[$con];
            
            if(isset($_conData) && is_array($_conData)) {
                
                /**
                 * Checks if the connection is already used. Increasing productivity
                 */
                if(!array_key_exists($con, self::$_connections)) {
                    self::$_connections[$con] = new \PDO((isset($_conData['driver']) ? $_conData['driver'] : 'mysql').':host='.$_conData['dbhost'].';dbname='.$_conData['dbname'], $_conData['dbuser'], $_conData['dbpass'], (isset($_conData['pdo_options']) ? $_conData['pdo_options'] : array()));
                }
                
                self::$_con = self::$_connections[$con];
            } else {
                throw new \Exception('Database configuration for '.ucfirst($con).' connection not found.');
            }
        }
        
        return $db;
    }
    
    /**
     * Get the PDO connection object
     * @return object
     */
    public static function getConnection() {
        return self::$_con;
    }

    
    /**
     * Create prepare statement
     * @static
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
     * @static
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
     * @static
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
     * @static
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
     * @static
     * @return array 
     */
    public static function fetchAssoc() {
        $db = self::connect();
        return $db->stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get a row
     * @static
     */
    public static function fetchRowAssoc() {
        $db = self::connect();
        return $db->stmt->fetch(\PDO::FETCH_ASSOC);
    }
 
    /**
     * Fetching of column
     * @static
     * @param string $column Column name
     * @return array
     */
    public static function fetchColumn($column) {
        $db = self::connect();
        return $db->stmt->fetchAll(\PDO::FETCH_COLUMN, $column);
    }

    /**
     * Get ID of the last inserted row
     * @static
     * @return int
     */
    public static function lastID() {
        return self::$_con->lastInsertId();
    }

    /**
     * Get count of the affected rows
     * @static
     * @return int
     */
    public function numRows() {
        $db = self::connect();
        return $db->stmt->rowCount();
    }

    /**
     * Get prepare statement's property. For advanced users!
     * @static
     * @return object
     */
    public static function getSTMT() {
        $db = self::connect();
        return $db->stmt;
    }

}

?>