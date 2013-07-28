<?php

namespace Maleeby\Libraries;
class Validation {
    /**
     * Data for validation
     * @var array
     */
    private static $data = array();
    
    /**
     * Validate a form
     * @param array $data Field rules
     * @return array
     */
    public function validate($data = array()) {
        /*
         * Set data
         */
        self::$data = $data;
        
        if(!isset($data['rules'])) {
            throw new \Exception('Field rules not set!');
        }
        
        /*
         * Fields looping
         */
        foreach($data['rules'] as $field=>$rules) {
            $result['fields'][$field] = $_POST[$field];
            /*
             * Rules looping
             */
            foreach($rules as $rule=>$val) {
                switch ($rule) {
                    case 'required':
                        if(!strlen($_POST[$field])) {
                            $result['err'][$field] = self::getMSG('required', $field); // Load message
                            $validated[$field][$rule] = TRUE;
                        }
                        break;
                    case 'minLength':
                        if(!$validated[$field]['required'] && strlen($_POST[$field]) < $val) {
                            $result['err'][$field] = sprintf(self::getMSG('minLength', $field), $val); // Load message
                            $validated[$field][$rule] = TRUE;
                        }
                        break;
                    case 'maxLength':
                        if(!$validated[$field]['minLength'] && strlen($_POST[$field]) > $val) {
                            $result['err'][$field] = sprintf(self::getMSG('maxLength', $field), $val); // Load message
                            $validated[$field][$rule] = TRUE;
                        }
                        break;
                    case 'equalTo':
                        if($_POST[$field] != $_POST[$val]) {
                            $result['err'][$field] = sprintf(self::getMSG('equalTo', $field), $val); // Load message
                            $validated[$field][$rule] = TRUE;
                        }
                        break;
                    case 'allowedValues':
                        if(!$validated[$field]['required'] && ((is_array($val) && !in_array($_POST[$field], $val)) || (!is_array($val) && $_POST[$field] != $val))) {
                            $result['err'][$field] = sprintf(self::getMSG('allowedValues', $field), $val); // Load message
                        }
                        break;
                    case 'email':
                        if(!$validated[$field]['required'] && !filter_var($_POST[$field], FILTER_VALIDATE_EMAIL)) {
                            $result['err'][$field] = sprintf(self::getMSG('email', $field), $val); // Load message
                        }
                        break;
                    case 'telephone':
                        if(!$validated[$field]['required'] && !preg_match('/^[0-9-+]+$/', $_POST[$field])) {
                            $result['err'][$field] = sprintf(self::getMSG('telephone', $field), $val); // Load message
                        }
                        break;
                    default:
                        break;
                }
            }
        }
                
        return $result;
    }
    
    /**
     * Get error message
     * @param string $rule
     * @param string $field
     * @return string
     */
    public static function getMSG($rule, $field = '') {
        /*
         * Get configuration
         */
        $_config = \Maleeby\Core::load()->getConfig()->validation;
        
        /*
         * Default strings
         */
        $strings = array(
            'required'=>'This field is required!',
            'minLength'=>'Please enter at least %s characters!',
            'maxLength'=>'Please enter no more than %s characters!',
            'equalTo'=>'Please enter the same value again!',
            'allowedValues'=>'Invalid value!',
            'email'=>'Invalid email!',
            'telephone'=>'Invalid telephone number!'
        );
        
        /*
         * Custom strings setted in configuration
         */
        if(isset($_config['strings']) && is_array($_config['strings'])) {
            $strings = array_merge($strings, $_config['strings']);
        }
        
        /*
         * Custom message
         */
        if(isset(self::$data['messages'][$field][$rule])) {
            return self::$data['messages'][$field][$rule];
        }
        
        return $strings[$rule];
    }
}
?>
