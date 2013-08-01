<?php

namespace Maleeby\Libraries;

/**
 * General functions class
 *
 * @author Yasen Georgiev <avbincco@gmail.com>
 * @link http://framework.maleeby.ygeorgiev.com/
 * @copyright Copyright &copy; 2013 Yasen Georgiev
 * @license http://framework.maleeby.ygeorgiev.com/#license
 * @package Libraries
 */
class Stuff {
    
    /**
     * Rederecting
     * @static
     * @param string $url Redirect URL
     */
    public static function redirect($url) {
        header('Location: '.BASE_URL.$url);
        die();
    }
    
    /**
     * String generating
     * @static
     * @param int $length String length
     * @return string Generated string
     */
    public static function generateString($length) {
        $random = str_shuffle('0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM');
        $cut = substr($random, 0, $length);
        return $cut;
    }
    
    
    /**
     * Transliterating of string
     * @static
     * @param string $text String to transliterate
     * @return string Transliterated string
     */
    public static function transliterate($text) {
        $en = array("a", "b", "v", "g", "d", "e", "zh", "z", "i", "i", "k", "l", "m", "n", "o", "p", "r", "s", "t", "u", "f", "h", "ts", "ch", "sh", "sht", "u", "io", "iu", "q", "a", "b", "v", "g", "d", "e", "zh", "z", "i", "ii", "k", "l", "m", "n", "o", "p", "r", "s", "t", "u", "f", "h", "c", "ch", "sh", "sht", "u", "io", "iu", "q", "ch", "sh", "sht", "_", "", "", "", "", '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''); //латинските букви
        $bg = array("а", "б", "в", "г", "д", "е", "ж", "з", "и", "й", "к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ь", "ю", "я", "А", "Б", "В", "Г", "Д", "Е", "Ж", "З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "У", "Ф", "Х", "Ц", "Ч", "Ш", "Щ", "Ъ", "Ь", "Ю", "Я", "ч", "ш", "щ", " ", ",", ".", "&", "'", '"', "!", "?", "(", "[", "]", ")", ";", ":", "-", "”", "„", "+", "“", "/"); //кирилица, съответстващ на латиницата по-горе
        $transform = str_replace($bg, $en, $text);
        $ready = str_replace("__", "_", $transform);
        return $ready;
    }    
    
    /**
     * Send email
     * @static
     * @param type $receiverEmail Receiver email
     * @param type $senderEmail Sender email
     * @param type $senderName Sender name
     * @param type $subject Email subject
     * @param type $content Email content
     * @return boolean
     */
    public static function sendMail($receiverEmail, $senderEmail, $senderName, $subject, $content) {
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers.='Content-type: text/html; charset=UTF-8' . "\r\n";
        $headers.='From: ' . iconv("UTF-8", "windows-1251", $senderName) . ' <' . $senderEmail . '>' . "\r\n";

        if (mail($receiverEmail, iconv("UTF-8", "windows-1251", $subject), stripslashes($content), $headers)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Search word in string
     * @static
     * @param string $string String in which to search
     * @param string $what Word to search
     * @return boolean
     */
    public static function search($string, $what) {
        $pos = strpos($string, $what);
        if ($pos !== false) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Translates date format to bulgarian months
     * @static
     * @param int $timestamp Timestamp
     * @return string Translated date format
     */
    public static function date($timestamp, $show = 'all') {
        $months = array("", "Януари", "Февруари", "Март", "Април", "Май", "Юни", "Юли", "Август", "Септември", "Октомври", "Ноември", "Декември");
        $m = date('n', $timestamp);
        $month = str_replace($m, $months[$m], $m);
        if ($show == 'all') {
            $format = date('d ' . $month . ' Y в H:i:s', $timestamp);
        } elseif ($show == 'short') {
            $format = date('d.' . $m . '.Y в H:i:s', $timestamp);
        }
        return $format;
    }
    
    /**
     * Get configuration file
     * @static
     * @param string $name Configuration file name
     * @return array Configuration
     */
    public static function getConfig($name) {
        return \Maleeby\Core::load()->getConfig()->$name;
    }
    
    /**
     * Remove folder and content in it
     * @static
     * @param string $dir Folder path
     */
    public static function removeDir($dir) { 
    if (is_dir($dir)) {
        $obj = scandir($dir);
        foreach ($obj as $object) { 
            if ($object != '.' && $object != '..') {
                if (is_dir($dir . '/' . $object)) { 
                    removeDir($dir . '/' . $object);  
                } else {
                    unlink($dir . '/' . $object); 
                }
            }
        }
        rmdir($dir);
    }
}
    
}

?>