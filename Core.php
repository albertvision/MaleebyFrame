<?php

namespace Maleeby;

/**
 * MaleebyFrame Core
 *
 * @author Yasen Georgiev <avbincco@gmail.com>
 * @link http://framework.maleeby.ygeorgiev.com/
 * @copyright Copyright &copy; 2013 Yasen Georgiev
 * @license http://framework.maleeby.ygeorgiev.com/#license
 * @package Core
 */
class Core {

    /**
     * Instance of this class
     * @access private
     * @static
     * @var object 
     */
    private static $_instance = null;

    /**
     * Configuration array
     * @access private
     * @var array 
     */
    private $config = array();

    /**
     * Instance of Routing class
     * @access private
     * @var object 
     */
    private $routing;


    /**
     * Instance of AutoLoader class
     * @var object 
     */
    public $autoload = null;

    private function __construct() {
        $this->checkPHPVersion();
        $this->autoload = AutoLoader::load();
        $this->autoload->setNamespace('Maleeby', realpath(__DIR__));
        set_exception_handler(array('\Maleeby\ErrorHandling', 'catchExceptions'));
    }

    /**
     * Get instance of this class
     * @access public
     * @return object Instance of this class
     */
    public static function load() {
        if (self::$_instance == null) {
            self::$_instance = new \Maleeby\Core();
        }
        return self::$_instance;
    }


    /**
     * Application starting
     * @access public
     */
    public function run() {        
        define('SYS_PATH', realpath(__DIR__));
        define('FRAME_VER', '0.3.1');
        
        $this->config = Config::load();
        $this->config->setConfigDir();
        $this->routing = Routing::load();
        $this->routing->division();
    }

    /**
     * Checks PHP Version.
     */
    private function checkPHPVersion() {
        if(phpversion() < 5.4) {
            die('PHP version too old. To use MaleebyFramework, you must install version 5.4 at least.');
        }
    }
    /**
     * Fix path
     * @access public
     * @static
     * @param string $path Path
     * @return string Fixed path
     */
    public static function fixPath($path) {
        return str_replace('\\', DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR, $path));
    }

    /**
     * Get Config class
     * @access public
     * @return type
     */
    public function getConfig() {
        return $this->config;
    }
    
    /**
     * @access public
     * @param type $name
     * @param type $arguments
     * @throws \Exception
     */
    public function __call($name, $arguments) {
        throw new \Exception('Method not found: ' . $name . '()');
    }

}

?>