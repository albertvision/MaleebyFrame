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
        if (is_array($data)) {
            $json = json_encode($data);
        } else {
            throw new \Exception('Invalid array provided', 500);
        }

        if (!$returnString) {
            header('Content-type: text/json');
            echo $json;
        } else {
            return $json;
        }
    }

    public static function xml($data, $returnString = false) {
        if (is_array($data)) {
            /**
             * Converts array to xml
             */
            $xml = new \SimpleXMLElement('<api/>');
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    array2xml($value, $xml->addChild($key));
                } else {
                    $xml->addChild($key, $value);
                }
            }

            $xml_data = $xml->asXML();
        } else {
            throw new \Exception('Invalid array provided', 500);
        }

        if (!$returnString) {
            header('Content-type: text/xml');
            echo $xml_data;
        } else {
            return $xml_data;
        }
    }

    public static function doesExist($type) {
        return method_exists(__CLASS__, $type);
    }

}
