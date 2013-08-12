<?php

namespace Maleeby;

/**
 * Routing class
 *
 * @author Yasen Georgiev <avbincco@gmail.com>
 * @link http://framework.maleeby.ygeorgiev.com/
 * @copyright Copyright &copy; 2013 Yasen Georgiev
 * @license http://framework.maleeby.ygeorgiev.com/#license
 * @package Core
 */
class Routing {

    /**
     * Instance of this class
     * @access private
     * @var object 
     */
    private static $_instance = null;

    /**
     * Instance of Core class 
     * @access private
     * @var object 
     */
    private $core;
    
    /**
     * Controller name
     * @var string
     */
    private $controller;
    
    /**
     * Method name
     * @var string
     */
    private $method;
    
    /**
     * Routing configuration
     * @var array
     */
    private $routesConfig;
    
    /**
     * Default route configuration
     * @var array
     */
    private $defaultRoute;

    /**
     * Current URI data
     * @var array
     */
    public $data = array();

    private function __construct() {
        ;
    }

    /**
     * Set instance of this class
     * @access public
     * @static
     * @return object Instance
     */
    public static function load() {
        if (self::$_instance == null) {
            self::$_instance = new Routing();
        }
        return self::$_instance;
    }

    /**
     * Parse URI
     * @return string
     */
    private function parseURI() {
        
        $_parsedScriptName = pathinfo($_SERVER['SCRIPT_NAME']);
        $_parsed = parse_url(str_replace($_parsedScriptName['basename'], '', str_replace($_parsedScriptName['dirname'] . '/', '', $_SERVER['REQUEST_URI'])));
        
        $_path = $_parsed['path'][0] == '/' ? substr($_parsed['path'], 1, (strlen($_parsed['path']) - 1)) : $_parsed['path']; // Removes first slash
        $_path = substr($_path, -1) == '/' ? substr($_path, 0, strlen($_path) - 1) : $_path; //Removes last slash
        
        return $_path;
    }
    
    /**
     * Routing of controllers/models/properties
     * @access public
     */
    public function division() {
        $this->core = Core::load(); //Load Core class
        
        $this->routesConfig = $this->core->getConfig()->routing; //Get routing configuration
        
        $_path = $this->parseURI();
        $params = array();

        if ($this->checkConfig()) {
            $this->defaultRoute = $this->routesConfig['*'];
            
            $namespace = $this->defaultRoute['namespace']; // Default namespace
            $this->core->autoload->setNamespace($namespace, realpath('..' . $this->core->getConfig()->main['controllers_path'])); //Set Controllers namespace

            foreach ($this->routesConfig as $k => $v) {
                $params = explode('/', str_replace($k . '/', '', $_path)); //Explode params
                $this->controller = $params[0];
                $this->method = $params[1];
                $pos = strpos(strtolower($_path) . '/', strtolower($k) . '/');
                
                if ($pos !== FALSE && $pos === 0) { //If there is a route for this URI
                    $namespace = isset($v['namespace']) ? $v['namespace'] : $this->defaultRoute['namespace']; //Set namespace
                    
                    /*
                     * Set default controller
                     */
                    if (strlen($_path) == strlen($k)) {
                        $this->controller = $this->getDefaultUriController($v['default_controller']);
                    }
                    
                    /*
                     * Renaming of a controller
                     */
                    if(isset($v['controller'][$this->controller]['rename'])) {
                        $this->controller = $v['controller'][$this->controller]['rename'];
                    }
                    
                    /*
                     * Set default method
                     */
                    if(!$params[1]) {
                        if ($v['controller'][$this->controller]['default_method']) { //If specific controller default method is set 
                            $this->method = $v['controller'][$this->controller]['default_method'];
                        } else {
                            $this->method = $this->getDefaultUriMethod($v['default_method']);
                        }
                    }
                    
                    /*
                     * Renaming of a method
                     */
                    if(isset($v['controller'][$this->controller]['method'][$this->method]['rename'])) {
                        $this->method = $v['controller'][$this->controller]['method'][$this->method]['rename'];
                    }
                    
                    break;
                }
            }
        } else {
            throw new \Exception('Invalid routing configuration!', 500);
        }
        
        /*
         * Unset the controller and the method
         */
        unset($params[0]);
        unset($params[1]);

        /*
         * Set default controller
         */
        if (!strlen($_path)) {
            $this->controller = $this->defaultRoute['default_controller'];
        }
        
        /*
         * Renaming of the default controller
         */
        if(isset($this->defaultRoute['controller'][$this->controller]['rename'])) {
            $this->controller = $this->defaultRoute['controller'][$this->controller]['rename'];
        }

        /*
         * Set default method
         */
        if (!$this->method) {
            $this->method = $this->getDefaultUriMethod($this->defaultRoute['controller'][$this->controller]['default_method']);
        }
        
        /*
         * Renaming of the default method
         */
        if(isset($this->defaultRoute['controller'][$this->controller]['method'][$this->method]['rename'])) {
            $this->method = $this->defaultRoute['controller'][$this->controller]['method'][$this->method]['rename'];
        }
        
        $this->controller = $namespace . '\\' . ucfirst(strtolower($this->controller));
        $controller = new $this->controller();

        /*
         * If method exists
         */
        if (method_exists($controller, $this->method)) {
            $reflection = new \ReflectionMethod($controller, $this->method);
            
            /*
             * If method is not accesible
             */
            if (!$reflection->isPublic()) {
                throw new \Exception('Method <b>' . $this->method . '()</b> in class <b>' . $this->controller . '</b> is not accessible!', 404);
            }
            
            /*
             * Set data in the array
             */
            $this->data = array(
                'controller' => $this->controller,
                'method' => $this->method,
                'params' => array_values($params)
            );
            
            /*
             * Set params
             */
            call_user_func_array(array($controller, $this->method), $params);
        } else {
            throw new \Exception('Method <b>' . $this->method . '()</b> in class <b>' . $this->controller . '</b> not found!', 404);
        }
    }

    /**
     * Configuration validation checking
     * @return boolean
     */
    private function checkConfig() {
        $config = $this->routesConfig;
        if(isset($config['*']['namespace']) && isset($config['*']['default_controller']) && isset($config['*']['default_method'])) {
            return true;
        }
        return false;
    }
    
    /**
     * Get default URI controller
     * @param string $controller Default controller
     * @return string
     */
    private function getDefaultUriController($controller) {
        if ($controller) {
            return $controller;
        }
        return $this->defaultRoute['default_controller'];
    }
    
    /**
     * Get default URI method
     * @param string $method Default method
     * @return string
     */
    private function getDefaultUriMethod($method) {
        if(isset($method)) {
            return $method;
        }
        return $this->defaultRoute['default_method'];
    }
    
    /**
     * Gets default controller setted in the main configuration
     * @return string Default controller
     * @access public
     */
    public function getDefaultController() {
        $contr = $this->core->getConfig()->main['default_controller'];
        if (!$contr) {
            $contr = 'DefaultController';
        }
        return $contr;
    }

    /**
     * Gets default controller method setted in the main configuration
     * @return string Default method
     * @access public
     */
    public function getDefaultMethod() {
        $contr = $this->core->getConfig()->main['default_method'];
        if (!$contr) {
            $contr = 'index';
        }
        return $contr;
    }

    /**
     * Get current controller
     * @return string
     */
    public function getController() {
        return $this->data['controller'];
    }

    /**
     * Get current method
     * @return string
     */
    public function getMethod() {
        return $this->data['method'];
    }

    /**
     * Get params
     * @param int $index Parameter ID - 0, 1, 2, etc.
     * @return string|array
     */
    public function getParam($index = NULL) {
        if($index == NULL) {
            return $this->data['params'];
        }
        return $this->data['params'][$index];
    }

}

?>