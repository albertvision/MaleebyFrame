<?php

namespace Maleeby;

class Routing {

    /**
     * Instance of this class
     * @var object 
     */
    private static $_instance = null;

    /**
     * Instance of Core class 
     * @var object 
     */
    private $core;

    /**
     * Get URI parameters
     * @var string
     */
    private $uri = null;

    private function __construct() {
        ;
    }

    /**
     * Set instance of this class
     * @return object Instance
     */
    public static function load() {
        if (self::$_instance == null) {
            self::$_instance = new Routing();
        }
        return self::$_instance;
    }
    
    /**
     * Routing of controllers/models/properties
     */
    public function division() {
        $this->core = \Maleeby\Core::load();
        $this->core->autoload->setNamespace('Controllers', realpath('..' . $this->core->getConfig()->main['controllers_path']));
        $_config = $this->core->getConfig()->main;
        $_routesConfig = $this->core->getConfig()->routing;
        
        $_parsedScriptName = pathinfo($_SERVER['SCRIPT_NAME']);
        $_parsed = parse_url(str_replace($_parsedScriptName['basename'] , '', str_replace($_parsedScriptName['dirname'].'/','',$_SERVER['REQUEST_URI'])));
        $_path = $_parsed['path'][0] == '/' ? substr($_parsed['path'], 1, (strlen($_parsed['path'])-1)) : $_parsed['path']; // Ако завършва на наклонена черта, то тогава я маха
        $_path = substr($_path, -1) == '/' ? substr($_path, 0, strlen($_path)-1) : $_path;
        $_params = array();        
        
        if(is_array($_routesConfig) && count($_routesConfig) > 0) {
            foreach($_routesConfig as $k=>$v) {
                $_params = explode('/',str_replace($k.'/', '', $_path));
                $controller = $_params[0];
                $method = $_params[1];
                $pos = strpos(strtolower($_path).'/', strtolower($k).'/');
                if($pos !== FALSE && $pos === 0) {
                    $namespace = $v['namespace'];
                    if(strlen($_path)==strlen($k)) {
                        if($v['default_controller']) {
                            $controller = $v['default_controller'];
                        } else {
                            $controller = $_config['default_controller'];
                            $method = $_config['default_method'];
                        }
                    }
                    if($v['controllers'][$controller]['default_method']!=NULL && !$_params[1]) {
                        if($v['controllers'][$controller]['default_method']) {
                            $method = $v['controllers'][$controller]['default_method'];
                        } else {
                            $method = $_config['default_method'];
                        }
                    } elseif(!$v['controllers'][$controller]['default_method'] && !$_params[1]) {
                        $method = $_config['default_method'];
                    }
                    break;
                }
            }
        } else {
            throw new \Exception('Routes configuration not found!', 500);
        }
        unset($_params[0]);
        unset($_params[1]);
        
        if(!strlen($_path)) {
            $controller = $_config['default_controller'];
            $method = $_config['default_method'];
        }
        if($method == NULL) {
            $method = $_config['default_method'];
        }
        if($namespace == NULL && $_routesConfig['*']['namespace']) {
            $namespace = $_routesConfig['*']['namespace'];
        } elseif($namespace == null && !$_routesConfig['*']['namespace'] ) {
            throw new \Exception('Default route in configuration missing!');
        }
        
        $controller = $namespace.'\\'.ucfirst(strtolower($controller));
        $contr = new $controller();
        
        if(method_exists($contr, $method)) {
            $reflection = new \ReflectionMethod($contr, $method);
            if (!$reflection->isPublic()) {
                throw new \Exception('Method <b>'.$method.'()</b> in class <b>'.$controller.'</b> not accessible!', 404);
            }
            call_user_func_array(array($contr, $method), $_params);
        } else {
            throw new \Exception('Method <b>'.$method.'()</b> in class <b>'.$controller.'</b> not found!', 404);
        }
    }

    /**
     * Gets default controller setted in the main configuration
     * @return string Default controller
     */
    public function getDefaultController() {
        $contr = \Maleeby\Core::load()->getConfig()->main['default_controller'];
        if (!$contr) {
            $contr = 'DefaultController';
        }
        return $contr;
    }

    /**
     * Gets default controller method setted in the main configuration
     * @return string Default method
     */
    public function getDefaultMethod() {
        $contr = \Maleeby\Core::load()->getConfig()->main['default_method'];
        if (!$contr) {
            $contr = 'index';
        }
        return $contr;
    }

}

?>