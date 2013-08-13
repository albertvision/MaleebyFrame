<?php

namespace Maleeby;

/**
 * Auto loading class
 *
 * @author Yasen Georgiev <avbincco@gmail.com>
 * @link http://framework.maleeby.ygeorgiev.com/
 * @copyright Copyright &copy; 2013 Yasen Georgiev
 * @license http://framework.maleeby.ygeorgiev.com/#license
 * @package Core
 */
class AutoLoader {

    /**
     * Instance of this class
     * @access private
     * @var object|null
     * @static
     */
    private static $instance = null;

    /**
     * Registered namespaces
     * @access private
     * @var array
     */
    private $namespaces = array();

    private function __construct() {
        $this->autoLoadRegister();
    }

    /**
     * Get autoload instance
     * @return object
     * @access public
     */
    public static function load() {
        if (self::$instance == null) {
            self::$instance = new \Maleeby\AutoLoader();
        }
        return self::$instance;
    }

    /**
     * Registers namespaces' path
     * @access public
     */
    private function autoLoadRegister() {
        spl_autoload_register(array('\Maleeby\AutoLoader', 'autoLoad'));
    }

    /**
     * Auto load method.
     * @param string $class Class to load
     * @access public
     * @throws \Exception 
     */
    public function autoLoad($class) {
        foreach ($this->namespaces as $namespace => $path) {
            if (strpos($class, $namespace) === 0) {
                if (strpos($namespace, 'Controller') === 0) {
                    $suffix = Core::load()->getConfig()->main['controllers_suffix'];
                    $fileType = 'Controller';
                    $errCode = 404;
                } elseif (strpos($namespace, 'Model') === 0) {
                    $suffix = Core::load()->getConfig()->main['models_suffix'];
                    $fileType = 'Model';
                    $errCode = 404;
                } elseif (strpos($class, 'Libraries') === 0 || strpos($class, 'Maleeby\Libraries') === 0) {
                    $fileType = 'Library';
                } else {
                    $fileType = 'File';
                }

                $class = $class . $suffix;
                $filename = Core::fixPath(str_replace($namespace, $path . DIRECTORY_SEPARATOR, $class) . '.php');
                $file = realpath($filename);

                if (file_exists($file)) {
                    include $file;
                } else {
                    throw new \Exception($fileType . ' not found: ' . $filename, $errCode);
                }
                break;
            }
        }
    }

    /**
     * Method that sets namespace's path
     * @access public
     * @param string $namespace Namespace
     * @param string $path Where does namespace is located
     * @throws \Exception
     */
    public function setNamespace($namespace, $path) {
        $namespace = trim($namespace);
        if ($namespace) {
            if ($path) {
                $realpath = realpath($path);
                if (file_exists($realpath)) {
                    $this->namespaces[$namespace . '\\'] = $realpath;
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
