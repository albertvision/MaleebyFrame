<?php

namespace Maleeby\Libraries;

/**
 * Cookies managing
 * 
 * @package MaleebyFrame Cookies Library
 */
class Cookies {
    
    /**
     * Set cookie
     * @param string $name
     * @param string $value
     * @param int $expire Expire time in seconds. Default: 1h
     * @param string $path Cookie path. Default: Home directory
     * @param string $domain Cookie domain.
     * @param bool $secure. Use HTTPS?
     */
    public static function set($name, $value, $expire = 3600, $path = '/', $domain = null, $secure = false, $httponly = false) {
        setcookie($name, $value, time()+$expire, $path, $domain, $secure, $httponly);
    }
    
    /**
     * Get cookies
     * @param string $name Cookie name
     * @return mixed Cookie value
     */
    public static function get($name) {
        return $_COOKIE[$name];
    }
    
    /**
     * Remove cookies
     * @param string $name Cookie name
     */
    public static function remove($name) {
        setcookie($name, '', 0, '/');
    }
    
    public static function all() {
        return $_COOKIE;
    }
}
?>
