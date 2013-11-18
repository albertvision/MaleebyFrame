<?php

namespace Maleeby;

/**
 * Display class.
 *
 * @author Yasen Georgiev <avbincco@gmail.com>
 * @link http://framework.maleeby.ygeorgiev.com/
 * @copyright Copyright &copy; 2013 Yasen Georgiev
 * @license http://framework.maleeby.ygeorgiev.com/#license
 * @package Core
 */
class Display extends Loader {
    /**
     * Display JSON data
     * @param array $args
     * @return type
     * @throws \Exception
     */
    public static function json($data, $returnString = false) {
        if(is_array($data)) {
            $json = json_encode($data);
        } else {
            throw new \Exception('Invalid array provided', 500);
        }
        
        if(!$returnString) {
            header('Content-type: text/json');
            echo $json;
        } else {
            return $json;
        }
    }
}
