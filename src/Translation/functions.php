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
function __($string, array $args = [], $context = 'default', $language = 'default')
{
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
            default:
                // Escaped and placeholder.
                $args[$key] = '<em>' . $value . '</em>';
                break;

        }
    }

    return strtr($translated, $args);

}

/**
 * Translates a string and returns it
 *
 * @param string $string
 * @param array $args
 * @return string
 */
function t($string, Array $args = [])
{
    return __($string, $args, I18N::getContext());
}

/**
 * Translates and echoes a string to convert
 *
 * @param string $string
 * @param array $args
 * @param string $context
 * @param string $language
 */
function _e($string, Array $args = [], $context = 'default', $language = 'default')
{
    echo __($string, $args, $context, $language);
}

/**
 * Translates with the correct (singular or plural) form
 *
 * @param string $string
 * @param string $plural
 * @param integer $count
 * @param array $args
 * @param string $context
 * @param string $language
 * @return string
 */
function _n($string, $plural, $count, Array $args = [], $context = 'default', $language = 'default')
{
    if ($count <= 1) {
        return __($string, $args, $context, $language);
    } else {
        return __($plural, $args, $context, $language);
    }
}

/**
 * Translates with the correct (singular or plural) form
 *
 * @param string $string
 * @param string $plural
 * @param int $count
 * @param array $args
 * @param string $language
 * @return string
 */
function tn($string, $plural, $count, Array $args = [], $language = 'default')
{
    return _n($string, $plural, $count, $args, I18N::getContext(), $language);
}

/**
 * Translates and echoes with the correct (singular or plural) form
 * @param string $string
 * @param string $plural
 * @param integer $count
 * @param array $args
 * @param string $context
 * @param string $language
 */
function _en($string, $plural, $count, Array $args = [], $context = 'default', $language = 'default')
{
    echo _n($string, $plural, $count, $args, $context, $language);
}
