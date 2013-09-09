<?php

namespace Maleeby\Libraries;

/**
 * Data hashing class
 *
 * @author Yasen Georgiev <avbincco@gmail.com>
 * @link http://framework.maleeby.ygeorgiev.com/
 * @copyright Copyright &copy; 2013 Yasen Georgiev
 * @license http://framework.maleeby.ygeorgiev.com/#license
 * @package Libraries
 */
class Hashing  {
    
    /**
     * Data hashing
     * @static
     * @param string|array $data Data to hashing
     * @param string $salt Hashing salt
     * @param string $algorithm Hashing algorithm
     * @return string Hashed data
     */
    public static function hash($data, $salt = null, $algorithm = null) {
        $_config = \Maleeby\Core::load()->getConfig()->hashing;
        if($salt == null) {
            $salt = $_config['salt'];
        }
        if($algorithm == null) {
            $algo = $_config['algorithm'];
            $algorithm = (isset($algo) && strlen($algo) ? $algo : 'sha256');
        }
        if(is_array($data)) {
            $data = implode('-', $data);
        }
        return hash_hmac(strtolower($algorithm), $data, $salt);
    }
    
    /**
     * MD5 Hashing
     * @static
     * @param string $data Data to hashing
     * @param bool $secured TRUE for secured hashing /via $this->encrypt/ or FALSE for MD5() function
     * @param string $salt Hashing salt
     * @return string Hashed data
     */
    public static function md5($data, $secured=true, $salt = null) {
        if($secured === TRUE) {
            return self::hash($data, $salt, 'md5');
        } else {
            return md5($data);
        }
    }
    
    
}
?>