<?php namespace Rocket\Translation;

interface I18NInterface {
    /**
     * Get the current language
     * @return string
     */
    public function getCurrent();

    /**
     * Get the current language id
     * @return int
     */
    public function getCurrentId();

    /**
     * Set the language to use
     *
     * @param  string $language
     * @return bool
     */
    public function setLanguage($language);

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
    public function languages($key = null, $subkey = null);

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
    public function translate($string, $context = 'default', $language = 'default');

    /**
     * Get the page's context
     *
     * @return string
     */
    public function getContext();
}