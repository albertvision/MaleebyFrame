<?php

namespace Maleeby;

/**
 * Configuration class
 *
 * @author Yasen Georgiev <avbincco@gmail.com>
 * @link http://framework.maleeby.ygeorgiev.com/
 * @copyright Copyright &copy; 2013 Yasen Georgiev
 * @license http://framework.maleeby.ygeorgiev.com/#license
 * @package Core
 */
class Config {

    /**
     * Configuration data
     * @access private
     * @var array|null 
     */
    private $data = null;
    
    /**
     * Configuration directory
     * @access public
     * @var null|string 
     */
    public $config_dir = null;
    
    /**
     * Instance of this class
     * @access private
     * @var null|object 
     * @static
     */
    private static $_instance = null;

    private function __construct() { }

    /**
     * Set instance of this class
     * @access public
     * @return object Instance
     */
    public static function load() {
        if (self::$_instance == null) {
            self::$_instance = new \Maleeby\Config();
        }
        return self::$_instance;
    }

    /**
     * Get configuration of any library
     * @access public
     * @param string $lib Library
     * @return array 
     */
    public function getLibSettings($lib) {
        if (!isset($this->data[$lib])) {
            $this->loadConfigFile($lib . '.php');
        }
        return $this->data[$lib];
    }

    /**
     * Load configuration file
     * @access public
     * @param string $file File to load
     * @throws \Exception
     */
    public function loadConfigFile($file) {
        $file = $this->config_dir . "/$file";
        $path = realpath($file);
        if ($path && is_readable($path)) {
            $key = str_replace('.php', '', basename($file));
            $this->data[$key] = include $path;
        } else {
            throw new \Exception('Configuration file not found: ' . Core::fixPath($file));
        }
    }

    /**
     * Set configuration directory
     * @access public
     * @param string $path New configuration's path
     * @throws Exception
     */
    public function setConfigDir($path) {
        $path = realpath($path);
        if (file_exists($path)) {
            $this->config_dir = $path;
        } else {
            throw new Exception('Configuration directory not found: ' . Core::fixPath($path));
        }
    }

    /**
     * Get configuration directory
     * @access public
     * @return string Configuration directory
     */
    public function getConfigDir() {
        return $this->config_dir;
    }

    /**
     * Get configuration of any library
     * @access public
     * @param string $name Library
     * @return array Configuration
     */
    public function __get($name) {
        return $this->getLibSettings($name);
    }

}

?>
