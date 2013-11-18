<?php

namespace Maleeby;

/**
 * Loader class. It's used by the applications. 
 * Loading: models, views, libraries, configs, errors, etc.
 *
 * @author Yasen Georgiev <avbincco@gmail.com>
 * @link http://framework.maleeby.ygeorgiev.com/
 * @copyright Copyright &copy; 2013 Yasen Georgiev
 * @license http://framework.maleeby.ygeorgiev.com/#license
 * @package Core
 */
class Loader {

    use Languages;

    /**
     * Loaded models
     * @access private
     * @var array
     */
    private $_sys_models = array();
    
    /**
     * Loaded controllers
     * @access private
     * @var array
     */
    private $_sys_controllers = array();

    /**
     * Loaded libraries
     * @access private
     * @var array 
     */
    private $_sys_libs = array();

    /**
     * Core class
     * @var object
     */
    protected $_sys = null;
    
    /**
     * Configuration class
     * @var string
     */
    protected $config;

    /**
     * Construct method
     */
    public function __construct() {
        $this->_sys = Core::load();
        $this->config = $this->_sys->getConfig();
    }
    /**
     * Load model
     * @access protected
     * @param string $name Model's name
     * @return object Model's instance
     * @throws \Exception
     */
    protected function model($name) {
        $this->_sys->autoload->setNamespace('Models', realpath(APP_PATH . $this->_sys->getConfig()->main['models_path']));
        if (!in_array($name, $this->_sys_models)) {
            $model = 'Models\\' . str_replace('/', '\\', $name);
            $suffix = $this->_sys->getConfig()->main['models_suffix'];
            //$file = Core::fixPath("../" . $this->_sys->getConfig()->main['models_path'] . '/' . $name . $suffix . ".php");
            $this->_sys_models[$name] = new $model();
        }
        return $this->_sys_models[$name];
    }
    
    /**
     * Load controller
     * @access protected
     * @param string $name Controller's name
     * @return object Controller's instance
     * @throws \Exception
     */
    protected function controller($name) {
        $this->_sys->autoload->setNamespace('Controllers', realpath(APP_PATH . $this->_sys->getConfig()->main['controllers_path']));
        if (!in_array($name, $this->_sys_controllers)) {
            $controller = 'Controllers\\' . str_replace('/', '\\', $name);
            $suffix = $this->_sys->getConfig()->main['controllers_suffix'];
            //$file = Core::fixPath("../" . $this->_sys->getConfig()->main['controllers_path'] . '/' . $name . $suffix . ".php");
            $this->_sys_controllers[$name] = new $controller();
        }
        return $this->_sys_controllers[$name];
    }

    /**
     * Load view
     * @access protected
     * @param string $name View's name
     * @param array $data Variables for giving
     * @param bool $returnString Return as string or via echo
     * @return string View's output
     * @throws \Exception
     */
    protected function view($name, $data = array(), $returnString = FALSE, $fullPath = NULL) {
        $data = (is_array($data) ? $data : array());

        if ($fullPath == NULL) {
            $name = (substr($name, -4) == '.php' ? substr($name, 0, strlen($name)-4) : $name);
            $loadFile = $name.'.php';
            $_sys_path = realpath(APP_PATH . $this->config()->main['views_path'] . DIRECTORY_SEPARATOR . "$name.php");
        } else {
            $loadFile = $name;
            $_sys_path = realpath($name);
        }

        if (is_readable($_sys_path) && is_file($_sys_path)) {
            ob_start();
            extract($data);
            include $_sys_path;

            $output = ob_get_clean();
            ob_end_flush();

            if ($returnString) {
                return $output;
            } else {
                echo $output;
            }
        } else {
            throw new \Exception("View not found: $loadFile");
        }
    }

    /**
     * Get library
     * @access protected
     * @deprecated
     * @param string $name Library name
     * @return object Library object
     */
    protected function library($name) {
        if (!in_array($name, $this->_sys_libs)) {
            $_sys_path = realpath(SYS_PATH . "/libraries/$name.php");
            $_app_path = realpath(APP_PATH . "/".$this->_sys->getConfig()->main['libraries_path']."/$name.php");

            if ($_sys_path && is_readable($_sys_path) && is_file($_sys_path)) {
                $namespace = 'Maleeby\Libraries\\';
            } else {
                $namespace = 'Libraries\\';
            }
            $library = $namespace . str_replace('/', '\\', $name);
            $this->_sys_libs[$name] = new $library();
        }
        return $this->_sys_libs[$name];
    }

    /**
     * Load configuration
     * @access protected
     * @param string $name Configuration file name
     * @return array
     */
    protected function config() {
        return Core::load()->getConfig();
    }

    public function __get($name) {
        return $this->model($name);
    }

}

?>