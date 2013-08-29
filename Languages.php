<?php

namespace Maleeby;

/**
 * Language trait
 *
 * @author Yasen Georgiev <avbincco@gmail.com>
 * @link http://framework.maleeby.ygeorgiev.com/
 * @copyright Copyright &copy; 2013 Yasen Georgiev
 * @license http://framework.maleeby.ygeorgiev.com/#license
 * @package Core
 */
trait Languages {

    /**
     * Loaded language files with the data in them
     * @var array
     */
    private $_sys_langs = array();

    /**
     * Current language
     * @var string
     */
    private $_sys_lang = NULL;

    /**
     * Set default language
     * @param string $name Language name. Two-words
     * @throws \Exception
     */
    protected function setLang($name) {
        if (is_string($name)) {
            $this->_sys_lang = $name;
        } else {
            throw new \Exception('Invalid default language data type!');
        }
    }

    /**
     * Get language string
     * @param string $key Language string key
     * @param string|null $lang Load from language
     * @return mixed
     */
    protected function lang($key, $lang = NULL) {
        $exp = explode('_', $key);
        $lang = ($this->_sys_lang != NULL && $lang == NULL ? $this->_sys_lang : $lang);
        $file_key = '$this->_sys_langs' . ($lang != NULL ? "['$lang']" : '' ) . "['$exp[0]']";

        if (isset($$file_key)) {
            return $$file_key[$exp[1]];
        } else {
            return $this->loadSingleLang($exp[0], $lang)[$exp[1]];
        }
    }

    /**
     * Load single file
     * @param string $file File name
     * @param string $language Load from language
     * @return array
     * @throws \Exception
     */
    private function loadSingleLang($file, $language = NULL) {
        $_suffix = $this->config()->main['languages_suffix'];

        $_lang_dir = ($language != NULL ? $language . DIRECTORY_SEPARATOR : '');
        $_lang_key = '$this->_sys_langs' . ($language != NULL ? "['$language']" : '') . "['$file']";

        $_filename = $this->_sys->fixPath(APP_PATH . $this->config()->main['languages_path'] . DIRECTORY_SEPARATOR . $_lang_dir . $file . $_suffix . '.php');
        $_path = realpath($_filename);

        if ($_path && is_readable($_path) && is_file($_path)) {
            include $_path;
            $$_lang_key = $lang;
        } else {
            throw new \Exception('Language file not found: ' . $_filename);
        }

        return $lang;
    }

}

?>