<?php

namespace Maleeby;

/**
 * Loader trait. It's used by the applications. 
 * Loading: models, views, libraries, configs, errors, etc.
 *
 * @author Yasen Georgiev <avbincco@gmail.com>
 * @link http://framework.maleeby.ygeorgiev.com/
 * @copyright Copyright &copy; 2013 Yasen Georgiev
 * @license http://framework.maleeby.ygeorgiev.com/#license
 * @package Core
 */
class Loader {

    /**
     * Loaded models
     * @access private
     * @var array
     */
    private $_sys_models = array();
    
    /**
     * Loaded libraries
     * @access private
     * @var array 
     */
    private $_sys_libs = array();
        
    /**
     * Load model
     * @access protected
     * @param string $name Model's name
     * @return object Model's instance
     * @throws \Exception
     */
    protected function model($name) {
        $this->setUp();
        $this->_sys_core->autoload->setNamespace('Models', realpath('..' . $this->_sys_core->getConfig()->main['models_path']));
        if (!in_array($name, $this->_sys_models)) {
            $model = 'Models\\' . str_replace('/','\\', $name);
            $suffix = $this->_sys_core->getConfig()->main['models_suffix'];
            $file = Core::fixPath("../".$this->_sys_core->getConfig()->main['models_path'].'/'.$name.$suffix.".php");
            $realpath = realpath($file);
            $this->_sys_models[$name] = new $model();
        }
        return $this->_sys_models[$name];
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
        $this->setUp();
        $data = (is_array($data) ? $data : array()); 
        $themeURL = (defined(THEME_URL) ? THEME_URL : BASE_URL); //Default theme URL
        
        if($fullPath == NULL) {
            $_sys_path = realpath( '..' . $this->_sys_core->getConfig()->main['views_path'] . DIRECTORY_SEPARATOR. "$name.php");
        } else {
            $_sys_path = realpath($name);
        }
        define('THEME_URL', $themeURL);
        
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
            throw new \Exception("View not found: views/$name.php");
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
        $this->setUp();
        if(!in_array($name, $this->_sys_libs)) {
            $_sys_path = realpath(SYS_PATH."/libraries/$name.php");
            $_app_path = realpath(APP_PATH."/libraries/$name.php");

            if($_sys_path && is_readable($_sys_path) && is_file($_sys_path)) {
                $namespace = 'Maleeby\Libraries\\';
            } else {
                $namespace = 'Libraries\\';
            }
            $library = $namespace.str_replace('/','\\',$name);
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
    
    /**
     * Set up class
     * @access private
     */
    private function setUp() {
        $this->_sys_core = Core::load();
        $this->_sys_core->autoload->setNamespace('Libraries', realpath('../libraries' ));
    }
    
    /**
     * Load file
     * @access private
     * @param string $file
     */
    private function load($file) {
        include $file;
    }
    
    public function __get($name) {
        $this->setUp();
        return $this->model($name);
    }
}

?>