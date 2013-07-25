<?php

namespace Maleeby;

class Loader {

    /**
     * Loaded models
     * @var array
     */
    private $_sys_models = array();
    
    public function __construct() {
        $this->setUp();
    }
    
    /**
     * Load model
     * @param string $name Model's name
     * @return object Model's instance
     * @throws \Exception
     */
    public function model($name) {
        $this->setUp();
        $this->_sys_core->autoload->setNamespace('models', realpath('..' . $this->_sys_core->getConfig()->main['models_path']));
        if (!in_array($name, $this->_sys_models)) {
            $model = 'models\\' . str_replace('/','\\', $name);
            $suffix = $this->_sys_core->getConfig()->main['models_suffix'];
            $file = \Maleeby\Core::fixPath("../".$this->_sys_core->getConfig()->main['models_path'].'/'.$name.$suffix.".php");
            $realpath = realpath($file);
            if ($realpath && is_file($realpath) && is_readable($realpath)) {
                $this->_sys_models[$name] = new $model();
            } else {
                throw new \Exception('Model path not found: models/' . $name.$suffix.'.php');
            }
        }
        return $this->_sys_models[$name];
    }

    public function themeView($name, $data = array(), $returnString = FALSE) {
        $theme = $this->model('admin/Themes')->getCurrent();
        return $this->view($name, $data, $returnString, $theme);
    }
    
    /**
     * Load view
     * @param string $name View's name
     * @param array $data Variables for giving
     * @param bool $returnString Return as string or via echo
     * @return string View's output
     * @throws \Exception
     */
    public function view($name, $data = array(), $returnString = FALSE, $fullPath = NULL) {
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
            throw new \Exception("Invalid view name: $name.$ext");
        }
    }
    
    public function helper($name) {
        if(is_array($name)) {
            foreach($name as $helperName) {
                $this->loadHelper($helperName);
            }
        } else {
            $this->loadHelper($name);
        }
    }
    
    private function loadHelper($name) {
        $_app_path = realpath(APP_PATH."/helpers/$name.php");
        $_sys_path = realpath(SYS_PATH."/helpers/$name.php");
        
        if($_app_path && is_readable($_app_path) && is_file($_app_path)) {
            include $_app_path;
        } elseif($_sys_path && is_readable($_sys_path) && is_file($_sys_path)) {
            include $_sys_path;
        } else {
            throw new \Exception('Invalid helper name: '.$name);
        }
    }
    
    private function setUp() {
        $this->_sys_core = \Maleeby\Core::load();
    }
    
    public function __get($name) {
        $this->setUp();
        return $this->model($name);
    }
}

?>