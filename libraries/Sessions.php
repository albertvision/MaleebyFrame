<?php

namespace Maleeby\Libraries;
 /**
  * Session managing
  */
class Sessions {    
    
    /**
     * Sets value of a session
     * @param string $name Session's name
     * @param string|array $value Session's value
     */
    public static function set($name, $value) {
        $_SESSION[$name] = $value;
    }
    
    /**
     * Get value of a session
     * @param string $name Session name
     * @return mixed Session value
     */
    public static function get($name) {
        return $_SESSION[$name];
    }
    
    /**
     * Remove session
     * @param string $name Session to remove
     */
    public static function remove($name) {
        $_SESSION[$name] = FALSE;
        unset($_SESSION[$name]);
    }
    
    /**
     * Get all sessions
     * @return array Sessions
     */
    public static function all() {
        return $_SESSION;
    }
    
    /**
     * Session destroying
     */
    public static function destroy() {
        session_destroy();
    }

}

?>
