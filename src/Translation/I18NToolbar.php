<?php
/**
 * Created by IntelliJ IDEA.
 * User: onigoetz
 * Date: 23.02.14
 * Time: 15:45
 */

namespace Rocket\Translation;

use Rocket\Translation\Model\Translation;

class I18NToolbar extends I18N
{
    protected function translateGetString($context, $language, $string_id)
    {
        $rows = Translation::where('string_id', $string_id)->get();

        if (!empty($rows)) {
            $text = false;
            foreach ($rows as $row) {
                $this->stringsRaw[$context][$string_id][$row->language_id] = $row;

                if ($row->language_id == $this->languagesIso[$language]['id']) {
                    $text = $row->text;
                }
            }
            if ($text !== false) {
                return $text;
            }
        }

        return false;
    }

    protected function translateInsertString($context, $text)
    {
        $translation = parent::translateInsertString($context, $text);

        $this->stringsRaw[$context][$translation->string_id]
        [$this->languagesIso[app('config')['app.locale']]['id']] = array(
            'id' => $translation->id,
            'string_id' => $translation->string_id,
            'text' => $text,
            'date_edition' => mysql_datetime()
        );

        return $translation;
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

        $this->strings[$language] = array();
        $this->languagesLoaded[] = $language;
    }
}
