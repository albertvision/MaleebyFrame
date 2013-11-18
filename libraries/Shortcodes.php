<?php

namespace Maleeby\Libraries;

/**
 * Shortcodes class
 *
 * @author Yasen Georgiev <avbincco@gmail.com>
 * @link http://framework.maleeby.ygeorgiev.com/
 * @copyright Copyright &copy; 2013 Yasen Georgiev
 * @license http://framework.maleeby.ygeorgiev.com/#license
 * @package Libraries
 */
class Shortcodes {

    /**
     * Loaded string
     * @var string 
     */
    private static $_string;
    /**
     * Opened but not closed shortcodes
     * @var array 
     */
    private static $_opened_tags = array();
    
    /**
     * Detected shortcodes
     * @var array
     */
    private static $_tags = array();
    
    /**
     * Registered shortcodes and thems settings
     * @var type 
     */
    private static $_registered = array();

    /**
     * Execute string
     * @param string $string String to execute
     * @return string String in which tags are replaced with thems callback method.
     */
    public static function execute($string) {
        self::$_string = trim($string);
        self::_detectShortCodes();
        self::_replaceShortcodes();

        return self::$_string;
    }

    /**
     * Detects shortcodes in the string
     * @return array Detected shortcodes
     */
    private static function _detectShortCodes() {
        $str = self::$_string;
        $words = array();

        while ($i <= strlen($str)) {
            $i++;
            $symbol = substr($str, $i - 1, 1);
            $last_symbol = $symbols[$i - 1];

            if ($symbol == '[' && $last_symbol != '\\') {
                self::$_opened_tags[] = $i;
            } elseif ($symbol == ']' && $last_symbol != '\\') {
                $last_opened_array_keys = array_keys(self::$_opened_tags);

                if (!count($last_opened_array_keys)) {
                    continue;
                }


                $last_opened_key = max($last_opened_array_keys);
                $last_opened = self::$_opened_tags[$last_opened_key]; // Get position of the last opened tag
                $tag_data_full = substr($str, $last_opened, $i - $last_opened - 1); // Get full tag code
                $tag_data = explode(' ', $tag_data_full, 2); // Get the tag name and its parameters

                if (array_key_exists($tag_data[0], self::$_registered)) {
                    $params = self::parseParameters($tag_data[1]);
                    self::$_tags[] = array(
                        'name' => $tag_data[0],
                        'full' => '[' . trim($tag_data_full) . ']',
                        'parameters' => $params,
                        'start' => $last_opened,
                        'end' => $i
                    );
                    unset(self::$_opened_tags[$last_opened_key]);
                }
            }

            $symbols[$i] = $symbol;
        }
        return self::$_tags;
    }

    /**
     * Parses string's parameters.
     * @param string $string String to find parameters in it.
     * @return array Shortcode parameters
     */
    private static function parseParameters($string) {
        $string = trim($string);
        $_opened_value = array();
        $_global_spaces = array();

        $exploded = explode('=', $string); //Parse by =

        foreach ($exploded as $exploded_key => $exploded_row) {
            $spaces = explode(' ', $exploded_row);
            foreach ($spaces as $space_key => $space) {
                $first_symb = substr($space, 0, 1);
                $last_symb = substr($space, -1);

                if (array_keys($_global_spaces)) {
                    $last_global_space = $_global_spaces[max(array_keys($_global_spaces))];
                }

                if (($first_symb == '"' && $last_symb != '"') || ($first_symb == "'" && $last_symb != "'")) {
                    $_opened_value[$last_global_space] = substr($space, 1);
                } elseif (($first_symb == '"' && $last_symb == '"') || ($first_symb == "'" && $last_symb == "'")) {
                    if (array_key_exists($last_global_space, $_opened_value)) {
                        unset($_opened_value[$last_global_space]);
                    }
                    $_tags[$last_global_space] = substr($space, 1, strlen($space) - 2);
                } elseif (($first_symb != '"' && $last_symb == '"') || ($first_symb != "'" && $last_symb == "'")) {
                    $tag_key = key(array_slice($_opened_value, -1, 1, TRUE));
                    $tag_val = end($_opened_value) . ' ' . substr($space, 0, strlen($space) - 1);

                    if (strlen($tag_key)) {
                        $_tags[$tag_key] = $tag_val;

                        unset($_opened_value[$tag_key]);
                    }
                } elseif ($space_key != max(array_keys($spaces)) && !count($_opened_value)) {
                    if (array_key_exists($space, $_opened_value)) {
                        unset($_opened_value[$last_global_space]);
                    }
                    $_tags[$space] = TRUE;
                } elseif (count($_opened_value)) {
                    $tag_key = key(array_slice($_opened_value, -1, 1, TRUE));
                    $tag_val = end($_opened_value) . ' ' . $space;
                    $_opened_value[$tag_key] = $tag_val;
                }
                $_global_spaces[] = $space;
            }
        }

        $last_space = end($_global_spaces);
        if (strlen($last_space) && (substr($last_space, 0, 1) != '"' && substr($last_space, -1) != '"') && (substr($last_space, 0, 1) != "'" && substr($last_space, -1) != "'")) {
            if (array_key_exists($last_space, $_opened_value)) {
                unset($_opened_value[$last_global_space]);
            }
            $_tags[$last_space] = TRUE;
        }

        return $_tags;
    }
    
    /**
     * Replaces a shortcode with its callback method
     * @throws \Exception Shortcode is not callable
     */
    private static function _replaceShortcodes() {
        $string = self::$_string;
        $tags = self::$_tags;

        foreach ($tags as $tag) {
            $tag_callback_method = self::$_registered[$tag['name']];
            if (is_callable($tag_callback_method)) {
                $callback_return = call_user_func($tag_callback_method, $tag['parameters']);
                $string = str_replace($tag['full'], $callback_return, $string);
            } else {
                throw new \Exception("Defined shortcode method for [$tag[name]] cannot be executed: $tag_callback_method()", 500);
            }
        }

        self::$_string = $string;
    }
    
    /**
     * Register a shortcode
     * @param string $shortcode Shortcode name
     * @param string $method Callback method
     * @throws \Exception 
     */

    public static function registerShortcode($shortcode, $method) {
        if (preg_match('/^[a-z0-9\-\_]+$/i', $shortcode)) {
            if (is_string($method)) {
                self::$_registered[$shortcode] = $method;
            } else {
                throw new \Exception("Invalid shortcode method name!", 500);
            }
        } else {
            throw new \Exception("Invalid shortcode name!", 500);
        }
    }

}
?>