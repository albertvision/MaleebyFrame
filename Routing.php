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
     * Routing configuration
     * @var array
     */
    private $_routesConfig;

    /**
     * Current URI data
     * @var array
     */
    private $_route_data = array();
    
    /**
     * Reserved method names
     * @var array
     */
    private $_reservedMethodNames = array('_methodNotFound');

    private function __construct() {
        $this->_routesConfig = Config::load()->routing; //Get routing configuration
        
        if($this->checkConfig()) {
            AutoLoader::load()->setNamespace($this->_routesConfig['namespace'], realpath(APP_PATH . Config::load()->main['controllers_path'])); //Set Controllers namespace
        } else {
            throw new \Exception('Invalid routing configuration', 500);
        }
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
     * Configuration validating
     * @return boolean
     */
    private function checkConfig() {
        if(isset($this->_routesConfig['namespace'], $this->_routesConfig['default_route'])) {
            if(!isset($this->_routesConfig['routes'])) {
                $this->_routesConfig['routes'] = array();
            }
            return true;
        }
        return false;
    }
    
    /**
     * Division of the URI
     * @throws \Exception
     */
    public function division() {
        $uri = $this->getReqURI();
        $route = $this->createRoute($uri);
        
        
        /*
         * Checks for matches in the routing configuration
         */
        foreach($this->_routesConfig['routes'] as $key=>$value) {
            if(preg_match($this->createURIRegexPattern($key), $uri, $output)) {
                foreach($output as $var_name=>$var_val) {
                    $value = str_replace('{'.$var_name.'}', $var_val, $value);
                }
                $route = $value;
                break;
            }
        }
        
        $parsed = $this->parseRoute($route);
        $parsed['controller'] = $this->_routesConfig['namespace'].'\\'.$parsed['controller'];        
        
        $this->_route_data = $this->parseRoute($this->createRoute($parsed));
        
        $controller = new $parsed['controller']();
        
        /*
         * If method exists
         */
        if (method_exists($controller, $parsed['method']) && !in_array($parsed['method'], $this->_reservedMethodNames)) {
            $reflection = new \ReflectionMethod($controller, $parsed['method']);

            /*
             * If method is not accesible
             */
            if (!$reflection->isPublic()) {
                throw new \Exception('Method <b>' . $parsed['method'] . '()</b> in class <b>' . $parsed['controller'] . '</b> is not accessible!', 404);
            }

            /*
             * Set params
             */
            call_user_func_array(array($controller, $parsed['method']), $parsed['params']);
        } elseif(method_exists($controller, '_methodNotFound')) {
            call_user_func_array(array($controller, '_methodNotFound'), [$parsed['method'], $parsed['params']]);
        } else {
            throw new \Exception('Method <b>' . $parsed['method'] . '()</b> in class <b>' . $parsed['controller'] . '</b> not found!', 404);
        }
        
    }
    
    
    /**
     * Parse URI
     * @return string
     */
    private function getReqURI() {
        $parsedScriptName = pathinfo($_SERVER['SCRIPT_NAME']);
        $parsed = parse_url(str_replace(array($parsedScriptName['basename'], $parsedScriptName['dirname'] . '/'), '', $_SERVER['REQUEST_URI']));
        
        return $this->clearURL($parsed['path']);
    }
    
    /**
     * Cleans the slashes in URL
     * @param string $url URL to clean
     * @return string
     */
    public function clearURL($url) {
        $url = str_replace('//', '/', $url);
        
        if($url[0] == '/') {
            $url = substr($url, 1, strlen($url));
        } if(substr($url, -1) == '/') {
            $url = substr($url, 0, strlen($url)-1);
        } if(strpos($url, '//') !== FALSE) {
            $url = $this->clearURL($url);
        }
        
        return $url;
    }
    
    /**
     * Parses a route to array. It contains 4 keys - controller, method, params and route.
     * 
     * @param string $route
     * @return array
     * @throws \Exception Invalid route
     */
    public function parseRoute($route) {
        if(is_string($route) && strpos($route, '@') !== FALSE && strlen($route)) {
            $_parse = explode('@', $route);
            
            $parsed['controller'] = $_parse[0];
            $parsed['method'] = $_parse[1];
            
            unset($_parse[0], $_parse[1]);
            
            $parsed['params'] = array_values($_parse);
            $parsed['route'] = array(
                'partial' => $parsed['controller'].'@'.$parsed['method'],
                'full' => $route
            );
        } else {
            throw new \Exception('Invalid route to parse ['.$route.']', 500);
        }
        
        return $parsed;
    }
    
    /**
     * Create a route by string or array
     * 
     * @param string|array $route String/Array to be converted to route
     * @return string
     * @throws \Exception
     */
    public function createRoute($route) {
        if(is_string($route)) {
            $default_route = $this->parseRoute($this->_routesConfig['default_route']);
            $parse = explode('/', $route);
            
            if(!isset($parse[0]) || !strlen($parse[0])) {
                $ready_route = $this->_routesConfig['default_route'];
            } elseif(!isset($parse[1])) {
                $ready_route = $route.'@'.$default_route['method'];
            } else {
                $ready_route = ucfirst(strtolower($parse[0])).'@'.  $parse[1];
                unset($parse[0], $parse[1]);
                if(count($parse)) {
                    $ready_route .= '@'.implode('@', $parse);
                }
            }
            
        } elseif(is_array($route)) {
            if(array_key_exists('controller', $route) && is_string($route['controller']) && strlen($route['controller'])) {
                $ready_route = $route['controller'].'@';
                if(array_key_exists('method', $route) && is_string($route['method']) && strlen($route['method'])) { 
                    $ready_route .= $route['method'];
                    
                    if(array_key_exists('params', $route) && is_array($route['params']) && count($route['params'])) {
                        $ready_route .= '@'.implode('@', $route['params']);
                    }
                    
                } else {
                    throw new \Exception('Invalid method submitted to parse', 500);
                }
            } else {
                throw new \Exception('Invalid controller submitted to parse', 500);
            }
        }
        
        return $ready_route;
    }
    
    /**
     * Changes the route. 
     * Eg. Controller@Method to Controller1@Method2
     * 
     * @param string $route Route to change
     * @param array $prop New route's properties
     * @return string The new route
     */
    public function changeRoute($route, $prop = array()) {
        $arr = array_merge($this->parseRoute($route), $prop);
        
        return $this->createRoute($arr);
    }
    
    /**
     * Creates a URI regex pattern
     * @param string $routePattern Route pattern
     * @return string Ready regex
     */
    public function createURIRegexPattern($routePattern) {
        preg_match_all('/{(\w+)}{1,}/', $routePattern, $required_params); // Required URI params
        preg_match_all('/{(\w+)\?}{0,}/', $routePattern, $optional_params); // Optional URI params
        
        $url_regex = $routePattern;
        
        foreach($required_params[1] as $match) {
            $url_regex = str_replace('{'.$match.'}', '(?<'.$match.'>\b\w*\b)', $url_regex);
        }
        foreach($optional_params[1] as $match) {
            $url_regex = str_replace('/{'.$match.'?}', '/{0,1}(?<'.$match.'>\b\w*\b){0,1}', $url_regex);
        }
        
        return '/'.str_replace('/', '\\/', $url_regex).'/';
    }


    /**
     * Get current controller
     * @return string
     */
    public function getController() {
        return $this->_route_data['controller'];
    }

    /**
     * Get current method
     * @return string
     */
    public function getMethod() {
        return $this->_route_data['method'];
    }

    /**
     * Get params
     * @param int $index Parameter ID - 0, 1, 2, etc.
     * @return string|array
     */
    public function getParam($index = NULL) {
        if ($index === NULL) {
            return $this->_route_data['params'];
        }
        return $this->_route_data['params'][$index];
    }
    
    /**
     * Get current route
     * @return string
     */
    public function getRoute($route_part = 'full') {
        return $this->_route_data['route'][$route_part];
    }

}

?>