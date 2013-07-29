<?php

namespace Maleeby;

class Config {

    private $data = null;
    public $config_dir = null;
    private static $_instance = null;

    private function __construct() {
        ;
    }

    /**
     * Set instance of this class
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
     * @param string $path New configuration's path
     * @throws Exception
     */
    public function setConfigDir($path = '../config') {
        $path = realpath($path);
        if (file_exists($path)) {
            $this->config_dir = $path;
        } else {
            throw new Exception('Configuration directory not found: ' . Core::fixPath($path));
        }
    }

    /**
     * Get configuration directory
     * @return string Configuration directory
     */
    public function getConfigDir() {
        return $this->config_dir;
    }

    /**
     * Get configuration of any library
     * @param string $name Library
     * @return array Configuration
     */
    public function __get($name) {
        return $this->getLibSettings($name);
    }

}

?>
