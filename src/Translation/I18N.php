<?php namespace Rocket\Translation;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\Session\Session;
use Illuminate\Routing\Router;
use Illuminate\Http\Request;
use Rocket\Translation\Model\Language;
use Rocket\Translation\Model\StringModel;
use Rocket\Translation\Model\Translation;

/**
 * Class I18N
 */
class I18N implements I18NInterface
{
    /**
     * An array of the loaded languages
     * @var array
     */
    protected $languagesLoaded = [];

    /**
     * An array of existing languages by ISO
     * @var array
     */
    protected $languagesIso = [];

    /**
     * An array of existing languages by ID
     * @var array
     */
    protected $languagesId = [];

    /**
     * @var integer
     */
    protected $defaultLanguage;

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
    protected $strings = [];

    /**
     * Context of the current page
     * @var string
     */
    protected $pageContext;

    /**
     * @var CacheRepository The cache to store the terms in
     */
    protected $cache;

    /**
     * @var Session The session store
     */
    protected $session;

    /**
     * @var string
     */
    protected $languageFilesPath;

    /**
     * @var Router
     */
    protected $router;

    /**
     * Prepare the translation service
     *
     * @param Application $app
     */
    public function __construct(Application $app, CacheRepository $cache, Session $session, ConfigRepository $config, Router $router, Request $request)
    {
        $this->languageFilesPath = $app->storagePath() . '/languages/';
        $this->cache = $cache;
        $this->session = $session;
        $this->router = $router;

        $lang = $this->cache->remember(
            'Lang::List',
            60 * 24,
            function () {
                return Language::all();
            }
        );

        foreach ($lang as $l) {
            $this->languagesIso[$l->iso] = $this->languagesId[$l->id] = [
                'id' => $l->id,
                'name' => $l->title,
                'iso' => $l->iso,
            ];
        }

        $this->defaultLanguage = $config['app.locale'];
        $fallback = $config['app.fallback_locale'];

        //current default language
        $language = $this->getCurrentLanguage($this->defaultLanguage, $fallback, $session, $request);
        $this->setLanguageForRequest($language);
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
     * @throws \RuntimeException if a default language cannot be found
     * @return string
     */
    public function getCurrentLanguage($locale, $fallback, Session $session, Request $request)
    {
        //1. detect user session
        $session_lang = $session->get('language');
        if (!empty($session_lang)) {
            return $session_lang;
        }

        //TODO :: move languages to subdomains
        //Special workaroud : only french for the moment
        if (defined('F_LANGUAGES') && !F_LANGUAGES) {
            return 'fr';
        }

        //2. detect browser language
        $browser_languages = $request->getLanguages();
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

        throw new \RuntimeException('Cannot find an adapted language');
    }

    /**
     * Load a language file
     *
     * @param  string $language
     * @return bool
     */
    public function loadLanguage($language)
    {
        if ($this->isLoaded($language)) {
            return;
        }

        $langfile = $language . '.php';

        $this->strings[$language] = [];

        // Determine where the language file is and load it
        $filePath = $this->languageFilesPath;
        if (file_exists($filePath . $langfile)) {
            $this->strings[$language] = include $filePath . $langfile;
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
     * @return int
     */
    public function getCurrentId()
    {
        return $this->currentLanguageId;
    }

    /**
     * Set the language to use
     *
     * @param  string $language
     * @throws \RuntimeException if the language doesn't exist
     */
    public function setLanguageForRequest($language)
    {
        if ($language == $this->currentLanguage) {
            return;
        }

        if (!$this->isAvailable($language)) {
            throw new \RuntimeException("Language '$language' is not available.");
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
     * Set the current language
     *
     * @param  string $language
     * @throws \RuntimeException if the language doesn't exist
     */
    public function setLanguageForSession($language)
    {
        if (!$this->isAvailable($language)) {
            throw new \RuntimeException("Language '$language' is not available.");
        }

        $this->session->put('language', $language);

        $this->setLanguageForRequest($language);
    }

    /**
     * Checks if a language is loaded or not
     *
     * @param  string $language
     * @return bool
     */
    protected function isLoaded($language)
    {
        return in_array($language, $this->languagesLoaded);
    }

    /**
     * Checks if a language is the default one
     *
     * @param  string $language
     * @return bool
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
     * @return bool
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
        $languages = [];
        foreach (static::languages() as $lang) {
            $languages[$lang['id']] = t($lang['name'], [], 'languages');
        }

        return $languages;
    }

    /**
     * Retreive a string to translate
     *
     * if it doesn't find it, put it in the database
     *
     * @param  string $keyString
     * @param  string $context
     * @param  string $language
     * @return string
     */
    public function translate($keyString, $context = 'default', $language = 'default')
    {
        
        if ($this->isDefault($language)) {
            $language = $this->getCurrent();
            $languageId = $this->getCurrentId();
        } else {
            if (!$this->isAvailable($language)) {
                throw new \RuntimeException("Language '$language' is not available");
            }
            $this->loadLanguage($language);
            $languageId = $this->languagesIso[$language]['id'];
        }

        // Read string from cache
        if (array_key_exists($context, $this->strings[$language]) &&
            array_key_exists($keyString, $this->strings[$language][$context])) {
            return $this->strings[$language][$context][$keyString];
        }

        // Read string from database
        $text = StringModel::getOrCreateTranslation($context, $keyString, $languageId, $this->languages($this->defaultLanguage, 'id'));
        if ($text) {
            // Store in cache for this request
            $this->strings[$language][$context][$keyString] = $text;

            return $text;
        }

        return $keyString;
    }

    /**
     * Get the page's context
     *
     * @return string
     */
    public function getContext()
    {
        if ($this->pageContext) {
            return $this->pageContext;
        }

        $current = $this->router->current();

        if (!$current) {
            return 'default';
        }

        if ($current->getName()) {
            return $this->pageContext = $current->getName();
        }

        if ($current->getActionName() != "Closure") {
            return $this->pageContext = $current->getActionName();
        }

        return $this->pageContext = trim($current->uri(), "/");
    }

    public function dumpCache()
    {
        $filePath = $this->languageFilesPath;

        if (!is_dir($filePath)) {
            mkdir($filePath, 0755, true);
            chmod($filePath, 0755);
        }

        foreach ($this->languages() as $lang => $d) {
            $strings = StringModel::select('string', 'text', 'context')
                ->where('language_id', $d['id'])
                ->join((new Translation)->getTable(), 'strings.id', '=', 'string_id', 'left')
                ->get();

            $final_strings = [];
            foreach ($strings as $s) {
                $final_strings[$s->context][$s->string] = $s->text;
            }

            file_put_contents("{$filePath}{$lang}.php", '<?php return ' . var_export($final_strings, true) . ';');
        }
    }
}
