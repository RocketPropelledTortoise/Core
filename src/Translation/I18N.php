<?php

/**
 * Translation management
 */

namespace Rocket\Translation;

use Exception;
use Illuminate\Foundation\Application as IlluminateApplication;
use Rocket\Translation\Model\Language;
use Rocket\Translation\Model\String;
use Rocket\Translation\Model\Translation;
use Request;

/**
 * Class I18N
 *
 * @package Youmewine
 */
class I18N
{

    /**
     * An array of the loaded languages
     * @var array
     */
    protected $languagesLoaded = array();

    /**
     * An array of existing languages by ISO
     * @var array
     */
    protected $languagesIso = array();

    /**
     * An array of existing languages by ID
     * @var array
     */
    protected $languagesId = array();

    /**
     * Language currently in use
     * @var string
     */
    protected $currentLanguage;

    /**
     * Language currently in use (ID)
     * @var string
     */
    protected $currentLanguageId;

    /**
     * All the translation strings
     * @var array
     */
    protected $strings = array();

    /**
     * Path to the language files
     * @var string
     */
    protected $filePath;

    /**
     * Context of the current page
     * @var string
     */
    protected $pageContext;

    /**
     * Strings cache
     * @var array
     */
    protected $stringsRaw = array();

    protected $cache;
    protected $session;
    protected $log;

    /**
     * Prepare the translation service
     *
     * @param IlluminateApplication $app
     */
    public function __construct(IlluminateApplication $app)
    {
        $this->filePath = $app['path.storage'] . '/languages/';
        $this->cache = $app['cache'];
        $this->session = $app['session'];
        $this->log = $app['log'];

        $lang = $this->cache->remember(
            'Lang::List',
            60 * 24,
            function () {
                return Language::all();
            }
        );

        foreach ($lang as $l) {
            $this->languagesIso[$l->iso] = $this->languagesId[$l->id] = array(
                'id' => $l->id,
                'name' => $l->title,
                'iso' => $l->iso
            );
        }

        $locale = $app['config']['app.locale'];
        $fallback = $app['config']['app.fallback_locale'];

        //current default language
        $language = $this->getDefaultLanguage($locale, $fallback);
        $this->setLanguage($language);

        $this->log->debug("Language Class Initialized");
    }

    /**
     * Detects the default languages in the following order :
     *
     * 1. Is a user session var defined ?
     * 2. Can we take it from the browser ?
     * 3. Take the site default
     *
     * @param $locale string
     * @param $fallback string
     * @return string
     * @throws Exception if a default language cannot be found
     */
    public function getDefaultLanguage($locale, $fallback)
    {
        //1. detect user session
        $session_lang = $this->session->get('language');

        if (!empty($session_lang)) {
            return $session_lang;
        }

        //TODO :: move languages to subdomains
        //Special workaroud : only french for the moment
        if (defined('F_LANGUAGES') && !F_LANGUAGES) {
            return 'fr';
        }

        //2. detect browser language
        $browser_languages = Request::getLanguages();

        //is one of them available ?
        foreach ($browser_languages as $lang) {
            if ($this->isAvailable($lang)) {
                $this->session->put('language', $lang);

                return $lang;
            }
        }

        //3. Site default
        if ($this->isAvailable($locale)) {
            return $locale;
        }

        //4. Site fallback
        if ($this->isAvailable($fallback)) {
            return $fallback;
        }

        throw new \Exception("Cannot find an adapted language");
    }

    /**
     * Set the current language
     *
     * @param  string $language
     * @return boolean
     */
    public function setCurrentLanguage($language)
    {
        if (!$this->isAvailable($language)) {
            return false;
        }

        $this->session->put('language', $language);

        $this->setLanguage($language);

        return true;
    }

    /**
     * Load a language file
     *
     * @param  string $language
     * @return boolean
     */
    public function loadLanguage($language)
    {
        if ($this->isLoaded($language)) {
            return;
        }

        $langfile = $language . '.php';

        $this->strings[$language] = array();

        // Determine where the language file is and load it
        if (file_exists($this->filePath . $langfile)) {
            $this->strings[$language] = include($this->filePath . $langfile);
        }

        $this->languagesLoaded[] = $language;
    }

    /**
     * Get the current language
     * @return string
     */
    public function getCurrent()
    {
        return $this->currentLanguage;
    }

    /**
     * Get the current language id
     * @return integer
     */
    public function getCurrentId()
    {
        return $this->currentLanguageId;
    }

    /**
     * Set the language to use
     *
     * @param  string $language
     * @return boolean
     */
    public function setLanguage($language)
    {
        if ($language == $this->currentLanguage) {
            return;
        }

        if (!$this->isLoaded($language)) {
            $this->loadLanguage($language);
        }

        switch ($language) {
            case 'fr':
                setlocale(LC_ALL, 'fr_FR.utf8', 'fr_FR.UTF-8', 'fr_FR@euro', 'fr_FR', 'french');
                break;
            case 'en':
                setlocale(LC_ALL, 'en_US.utf8', 'en_US.UTF-8', 'en_US');
                break;
            case 'de':
                setlocale(LC_ALL, 'de_DE.utf8', 'de_DE.UTF-8', 'de_DE@euro', 'de_DE', 'deutsch');
                break;
        }

        $this->currentLanguage = $language;
        $this->currentLanguageId = $this->languagesIso[$language]['id'];
    }

    /**
     * Checks if a language is loaded or not
     *
     * @param  string $language
     * @return boolean
     */
    protected function isLoaded($language)
    {
        return in_array($language, $this->languagesLoaded);
    }

    /**
     * Checks if a language is the default one
     *
     * @param  string $language
     * @return boolean
     */
    protected function isDefault($language)
    {
        if ($language == 'default' or $this->currentLanguage == $language) {
            return true;
        }

        return false;
    }

    /**
     * Checks if the language is availavble
     *
     * @param  string $language
     * @return boolean
     */
    protected function isAvailable($language)
    {
        return array_key_exists($language, $this->languagesIso);
    }

    /**
     * Retrieve languages.
     *
     * this is a hybrid method.
     *
     *
     *     I18N::languages();
     *     returns ['fr' => ['id' => 1, 'name' => 'francais', 'iso' => 'fr'], 'en' => ...]
     *
     *
     *     I18N::languages('fr');
     *     returns ['id' => 1, 'name' => 'francais', 'iso' => 'fr']
     *
     *
     *     I18N::languages(1);
     *     returns ['id' => 1, 'name' => 'francais', 'iso' => 'fr']
     *
     *
     *     I18N::languages('fr', 'id');
     *     returns 1
     *
     * @param int|string $key
     * @param string $subkey
     * @return array
     */
    public function languages($key = null, $subkey = null)
    {
        if ($key === null) {
            return $this->languagesIso;
        }

        if (is_int($key)) {
            if (is_null($subkey)) {
                return $this->languagesId[$key];
            }
            return $this->languagesId[$key][$subkey];
        }

        if (is_null($subkey)) {
            return $this->languagesIso[$key];
        }
        return $this->languagesIso[$key][$subkey];
    }

    public function languagesForSelect()
    {
        $languages = array();
        foreach (static::languages() as $lang) {
            $languages[$lang['id']] = __($lang['name'], array(), 'languages');
        }
        return $languages;
    }

    protected function translateGetString($context, $language, $string_id)
    {
        $row = Translation::select('text')
            ->where('string_id', $string_id)
            ->where('language_id', $this->languagesIso[$language]['id'])
            ->first();

        if ($row !== null) {
            return $row->text;
        }

        return false;
    }

    protected function translateInsertString($context, $text)
    {
        $string = new String();
        $string->date_creation = mysql_datetime();
        $string->context = $context;
        $string->string = $text;
        $string->save();

        //insertion de la traduction par défaut.
        $translation = new Translation();
        $translation->string_id = $string->id;
        $translation->language_id = $this->languagesIso[app('config')['app.locale']]['id'];
        $translation->date_edition = mysql_datetime();
        $translation->text = $text;
        $translation->save();

        return $translation;
    }

    /**
     * Retreive a string to translate
     *
     * if it doesn't find it, put it in the database
     *
     * @param  string $string
     * @param  string $context
     * @param  string $language
     * @return string
     */
    public function translate($string, $context = 'default', $language = 'default')
    {
        if ($this->isDefault($language)) {
            $language = $this->currentLanguage;
        } else {
            $this->setLanguage($language);
        }

        //get string from cache
        if (array_key_exists($context, $this->strings[$language]) &&
            array_key_exists($string, $this->strings[$language][$context])) {
            return $this->strings[$language][$context][$string];
        }

        //check in db
        $db_string = String::select('id', 'date_creation')
            ->where('string', $string)
            ->where('context', $context)
            ->first();

        if (!$db_string) {
            $this->translateInsertString($context, $string);
            return $string;
        }

        $text = $this->translateGetString($context, $language, $db_string->id);
        if ($text) {
            $this->strings[$language][$context][$string] = $text;
            return $text;
        }

        return $string;
    }

    /**
     * Get the cached strings
     *
     * @return array
     */
    public function getRawStrings()
    {
        return $this->stringsRaw;
    }

    /**
     * Get the page's context
     *
     * @todo Convert ContextGetter to Laravel
     * @return string
     */
    public function getContext()
    {
        if ($this->pageContext) {
            return $this->pageContext;
        }

        $context = '';

        //Directory
        if (CI()->router->directory) {
            $context .= CI()->router->directory;
        }

        //Class
        $context .= strtolower(
            substr(CI()->router->class, 0, strlen(CI()->router->class) - strlen(CI()->router->suffix()))
        );

        //Method
        $context .= '/' . CI()->router->method;

        $this->pageContext = $context;

        return $context;
    }
}
