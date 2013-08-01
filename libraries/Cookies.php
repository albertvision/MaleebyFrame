<?php

namespace Maleeby\Libraries;

/**
 * Cookies managing class
 *
 * @author Yasen Georgiev <avbincco@gmail.com>
 * @link http://framework.maleeby.ygeorgiev.com/
 * @copyright Copyright &copy; 2013 Yasen Georgiev
 * @license http://framework.maleeby.ygeorgiev.com/#license
 * @package Libraries
 */
class Cookies {
    
    /**
     * Set cookie
     * @static
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
     * @static
     * @param string $name Cookie name
     * @return mixed Cookie value
     */
    public static function get($name) {
        return $_COOKIE[$name];
    }
    
    /**
     * Remove cookies
     * @static
     * @param string $name Cookie name
     */
    public static function remove($name) {
        setcookie($name, '', 0, '/');
    }
    
    /**
     * Get all cookies
     * @static
     * @return array All created cookies
     */
    public static function all() {
        return $_COOKIE;
    }
}
?>
