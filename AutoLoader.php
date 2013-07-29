<?php

namespace Maleeby;
class AutoLoader {
    private static $instance = null;
    private $namespaces = array();
    
    private function __construct() {
        $this->autoLoadRegister();
    }
    
    /**
     * Get autoload instance
     * @return object
     */
    public static function load() {
        if (self::$instance == null ) {
            self::$instance = new \Maleeby\AutoLoader();
        }
        return self::$instance;
    }
    
    /**
     * Registers namespaces' path
     */
    private function autoLoadRegister() {
        spl_autoload_register(array('\Maleeby\AutoLoader','autoLoad'));
    }
    
    /**
     * Auto load method.
     * @param string $class Class to load
     * @throws \Exception 
     */
    public function autoLoad($class) {
        foreach($this->namespaces as $namespace=>$path) {
            if(strpos($class, $namespace) === 0) {
                if(strpos($namespace, 'Controller') !== FALSE) {
                    $suffix = Core::load()->getConfig()->main['controllers_suffix'];
                    $errCode = 404;
                } elseif(strpos($namespace, 'Model') !== FALSE) {
                    $suffix = Core::load()->getConfig()->main['models_suffix'];
                    $errCode = 404;
                } 
                $class = $class.$suffix;
                $filename = Core::fixPath(str_replace($namespace, $path.DIRECTORY_SEPARATOR, $class).'.php');
                $file = realpath($filename);
                if(file_exists($file)) {
                    include $file;
                } else {
                    throw new \Exception('File not found: '.$filename, $errCode);
                }
                break;
            }
        }
    }
    
    /**
     * Method that sets namespace's path
     * @param string $namespace Namespace
     * @param string $path Where does namespace is located
     * @throws \Exception
     */
    public function setNamespace($namespace, $path) {
        $namespace = trim($namespace);
        if($namespace) {
            if($path) {
                $realpath = realpath($path);
                if(file_exists($realpath)) {
                    $this->namespaces[$namespace.'\\'] = $realpath;
                } else {
                    throw new \Exception('Invalid namespace path');
                }
            } else {
                throw new \Exception('Invalid namespace path');
            }
        } else {
            throw new \Exception('Invalid namespace name!');
        }
    }
}


?>
