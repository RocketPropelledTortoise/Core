<?php

/**
 * Language related functions
 *
 * @author Stéphane Goetz
 */

/**
 * Translates a string and returns it
 *
 * @param string $string
 * @param array $args
 * @param string $context
 * @param string $language
 * @return string
 */
function t($string, array $args = [], $context = null, $language = 'default')
{
    if ($context === null) {
        $context = I18N::getContext();
    }

    //get translation
    $translated = I18N::translate($string, $context, $language);

    //if there are some arguments
    if (empty($args)) {
        return $translated;
    }

    // Transform arguments before inserting them.
    foreach ($args as $key => $value) {
        switch ($key[0]) {
            case '@':
                // Escaped only.
                $args[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                break;
            case '%':
                // Escaped and placeholder.
                $args[$key] = '<em>' . $value . '</em>';
                break;
            case '!':
            default:
                //do nothing
        }
    }

    return strtr($translated, $args);

}

/**
 * Translates with the correct (singular or plural) form
 *
 * @param string $string
 * @param string $plural
 * @param array $args
 * @param string $context
 * @param string $language
 * @return string
 */
function tn($string, $plural, array $args = [], $context = 'default', $language = 'default')
{
    if ($args['#'] <= 1) {
        return t($string, $args, $context, $language);
    } else {
        return t($plural, $args, $context, $language);
    }
}
