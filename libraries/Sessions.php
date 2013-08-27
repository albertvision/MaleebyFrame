<?php

namespace Maleeby\Libraries;

/**
 * Session managing class
 *
 * @author Yasen Georgiev <avbincco@gmail.com>
 * @link http://framework.maleeby.ygeorgiev.com/
 * @copyright Copyright &copy; 2013 Yasen Georgiev
 * @license http://framework.maleeby.ygeorgiev.com/#license
 * @package Libraries
 */
class Sessions {    
    
    /**
     * Start session
     */
    public static function start() {
        session_start();
    }
    
    /**
     * Sets value of a session
     * @static
     * @param string $name Session's name
     * @param string|array $value Session's value
     */
    public static function set($name, $value) {
        $_SESSION[$name] = $value;
    }
    
    /**
     * Get value of a session
     * @static
     * @param string $name Session name
     * @return mixed Session value
     */
    public static function get($name) {
        return $_SESSION[$name];
    }
    
    /**
     * Remove session
     * @static
     * @param string $name Session to remove
     */
    public static function remove($name) {
        $_SESSION[$name] = FALSE;
        unset($_SESSION[$name]);
    }
    
    /**
     * Get all sessions
     * @static
     * @return array Sessions
     */
    public static function all() {
        return $_SESSION;
    }
    
    /**
     * Session destroying
     * @static
     */
    public static function destroy() {
        session_destroy();
    }

}

?>
