<?php

namespace Maleeby\Libraries;

/**
 * Mail class
 *
 * @author Yasen Georgiev <avbincco@gmail.com>
 * @link http://framework.maleeby.ygeorgiev.com/
 * @copyright Copyright &copy; 2013 Yasen Georgiev
 * @license http://framework.maleeby.ygeorgiev.com/#license
 * @package Libraries.PHPMailer
 */
class Mail {

    /**
     * Instance of PHPMailer class
     * @var null|object
     */
    private static $_mailer = null;

    /**
     * Loading of PHPMailer class
     * @return object
     */
    private static function loadMailer() {
        self::$_mailer = \Maleeby\Libraries\PHPMailer\PHPMailer::load();
        return self::$_mailer;
    }

    /**
     * Setting up of this class
     * @return object Instance of PHPMailer class
     * @throws \Exception
     */
    public function setUp() {
        $mailer = self::loadMailer();
        $config = \Maleeby\Core::load()->getConfig()->mail;
        if (is_array($config)) {
            /**
             * If GLOBAL Debug mode is on, then don't show Mail's log
             */
            if (\Maleeby\Core::load()->getConfig()->main['debug'] != TRUE) {
                $mailer->SMTPDebug = FALSE;
            } else {
                $mailer->Debugoutput = 'html';
                $mailer->SMTPDebug = $config['debug'];
            }
            self::smtpConnect($mailer, $config); //SMTP Connect if it's set in the configuration            
        } else {
            throw new \Exception('Invalid mail configuration!', 500);
        }

        return $mailer;
    }

    /**
     * SMTP Connect
     * @param object $mailer Instance of PHPMailer class
     * @param array $config Mail configuration
     */
    public static function smtpConnect($mailer, $config) {
        $mailer->IsSMTP();
        $mailer->Host = $config['smtp']['host'];
        $mailer->Port = $config['smtp']['port'];
        $mailer->SMTPSecure = $config['smtp']['secure'];

        $mailer->SMTPAuth = $config['smtp']['auth'];
        $mailer->Username = $config['smtp']['user'];
        $mailer->Password = $config['smtp']['pass'];
    }

    /**
     * Sends an email 
     * @param string $receiver Receiver's email
     * @param string $subject Subject
     * @param string $message Message
     * @param string $sender Sender's email. E.g. "Ivan Petrov <ivan@me.com>" or just "ivan@me.com"
     * @param string|null $attachment Email attachment path
     * @return boolean
     */
    public static function send($receiver, $subject, $message, $sender, $attachment = null) {
        $mailer = self::setUp();
        $mailer->AddAddress($receiver);
        $mailer->Subject = $subject;
        $mailer->MsgHTML($message);
        $sender = explode('<', substr($sender, -1) ? substr($sender, 0, strlen($sender) - 1) : $sender, 2);
        
        /**
         * Sender
         */
        if (count($sender) == 1) {
            $mailer->SetFrom($sender[0]);
        } else {
            $mailer->SetFrom($sender[1], $sender[0]);
        }
        
        /**
         * Atachment
         */
        if ($attachment != NULL) {
            $mailer->AddAttachment($attachment);
        }

        /**
         * Send message
         */
        if ($mailer->Send()) {
            return true;
        } else {
            return false;
        }
    }

}

?>
