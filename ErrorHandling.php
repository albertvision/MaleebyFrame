<?php

namespace Maleeby;

/**
 * Error handling class
 *
 * @author Yasen Georgiev <avbincco@gmail.com>
 * @link http://framework.maleeby.ygeorgiev.com/
 * @copyright Copyright &copy; 2013 Yasen Georgiev
 * @license http://framework.maleeby.ygeorgiev.com/#license
 * @package Core
 */
class ErrorHandling {
    
    private function  __construct() { }
    
    /**
     * Catchs exceptions
     * @access public
     * @static
     * @param object \Exception $err Exception object
     */
    public static function catchExceptions(\Exception $err) {
        header('HTTP/1.0 '.$err->getCode().' '.self::getErrorDesc($err->getCode()));
        if (Core::load()->getConfig()->main['debug'] === TRUE) {
            echo '<p><b>System error: </b>' . $err->getMessage() . ' in ' . $err->getFile() . ':' . $err->getLine() . '</b></p>';
        } else {
            $errCode = ($err->getCode() != 0 ? $err->getCode() : 500);
            $output = self::loadErrorFile($errCode);
            echo $output;
        }
        self::logError($err);
        die();
    }
    
    /**
     * Loads error's file
     * @access public
     * @static
     * @param int $errCode Error code
     * @return bool|string
     */
    public static function loadErrorFile($errCode) {
        $_sys_errorFilePath = realpath( APP_PATH . Core::load()->getConfig()->main['errors_path']."/$errCode.php" );
        if(!$_sys_errorFilePath) {
            return '<h1>'.self::getErrorDesc($errCode).'!</h1> <h2>System code: ' . $errCode . '</h2><hr><i>Please, contact with <a href="mailto: '.$_SERVER['SERVER_ADMIN'].'">administrator</a> and say him about this error!</i>';
        } else {
            ob_start();
            include $_sys_errorFilePath;
            $output = ob_get_clean();
            ob_end_flush();
            
            return $output;
        }
    }
    
    /**
     * Error logging in file
     * @access public
     * @static
     * @param object $err Exception object
     */
    public static function logError($err) {
        if(is_object($err)) {
            $fileName = realpath($err->getFile());
            $errCode = ($err->getCode() != 0 ? $err->getCode() : 500);
            $errLine = $err->getLine();
            $errMsg = $err->getMessage();
        } else {
            $fileName = realpath($err[0]);
            $errCode = $err[1];
            $errMsg = $err[3];
        }
        $dir = realpath('../');
        $file = $dir . '/log.out';
        file_put_contents($file, date('d-m-Y H:i:s') . ' [Err. ' . $errCode . '][' . $fileName . ($errLine != NULL ? ':' . $errLine : null ) .'] >>> ' . strip_tags($errMsg) . "\n", FILE_APPEND);
    }
    
    /**
     * Get error's description
     * @access public
     * @static
     * @param int $errCode Error's code
     * @return string Error's description
     */
    public static function getErrorDesc($errCode) {
        $http_codes = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Switch Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            418 => 'I\'m a teapot',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Unordered Collection',
            426 => 'Upgrade Required',
            449 => 'Retry With',
            450 => 'Blocked by Windows Parental Controls',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended'
        );
        return $http_codes[$errCode];
    }
    
}

?>
