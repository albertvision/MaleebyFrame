<?php

namespace Maleeby;

class Core {

    /**
     * Instance of this class
     * @var object 
     */
    private static $_instance = null;

    /**
     * Configuration array
     * @var array 
     */
    private $config = array();

    /**
     * Instance of Routing class
     * @var object 
     */
    private $routing;


    /**
     * Instance of AutoLoader class
     * @var object 
     */
    public $autoload = null;

    private function __construct() {
        $this->autoload = AutoLoader::load();
        $this->autoload->setNamespace('Maleeby', realpath(__DIR__));
        set_exception_handler(array('\Maleeby\ErrorHandling', 'catchExceptions'));
    }

    /**
     * Get instance of this class
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
     */
    public function run() {
        define('SYS_PATH', realpath(__DIR__));
        define('FRAME_VER', '0.1.2');
        
        $this->config = \Maleeby\Config::load();
        $this->config->setConfigDir();
        $this->routing = \Maleeby\Routing::load();
        $this->routing->division();
    }

    /**
     * Fix path
     * @param string $path Path
     * @return string Fixed path
     */
    public static function fixPath($path) {
        return str_replace('\\', DIRECTORY_SEPARATOR, str_replace('/', DIRECTORY_SEPARATOR, $path));
    }

    /**
     * Get Config class
     * @return type
     */
    public function getConfig() {
        return $this->config;
    }
    
    /**
     * 
     * @param type $name
     * @param type $arguments
     * @throws \Exception
     */
    public function __call($name, $arguments) {
        throw new \Exception('Method not found: ' . $name . '()');
    }

}

?>