<?php


/**
 * Adds the translate bar to the screen if needed
 *
 * @global MY_Lang $LANG
 * @param string $output
 * @return string
 */
Event::listen(
    'output',
    function ($output) {

        if (true) {
            return;
        }

        //TODO :: reimplement translation interface

        global $LANG;

        $final_contexts = '';
        $final_strings = '';
        $final_translations = '';
        $current_id = I18N::getCurrentId();

        //get main context's strings
        $main_context = I18N::getContext();
        $raw_strings = I18N::getRawStrings();

        $query = DB::table('strings');

        if (array_key_exists($main_context, $raw_strings)) {
            $query->whereNotIn('id', array_keys($raw_strings[$main_context]));
        }

        $strings = $query->select('id')->where('context', $main_context)->get();

        $unused = [];
        if (!empty($strings)) {
            $unused = array_map(
                function ($arg) {
                    return $arg->id;
                },
                $strings
            );

            $rows = DB::table('translations')->whereIn('string_id', $unused)->get();

            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $raw_strings[$main_context][$row->string_id][$row->language_id] = $row;
                }
            }
        }

        foreach ($raw_strings as $context => $strings) {
            $final_contexts .= '<div class="t_line context" title="strings_' . str_replace('/', '_', $context) . '">' . $context . '</div>';

            $final_strings .= '<div id="strings_' . str_replace('/', '_', $context) . '" class="t_internal" style="display:none;">';
            foreach ($strings as $string_id => $string) {
                $class = (in_array($string_id, $unused)) ? ' unused' : '';
                if (array_key_exists($current_id, $string)) {
                    $final_strings .= '<div class="t_line string' . $class . '" title="string_' . $string_id . '">' . $string[$current_id]->text . '</div>';
                } else {
                    //not translated
                    $final_strings .= '<div class="t_line string not' . $class . '" title="string_' . $string_id . '">' . $string[1]->text . '</div>';
                }

                $final_translations .= '<div id="string_' . $string_id . '" class="t_internal" style="display:none;">';
                $final_translations .= '<div class="t_links t_bar">';

                $link = icon('pencil') . ' ' . t('Editer', [], 'admin/lang/bar');
                $final_translations .= anchor_modal('admin/lang/string_edit/' . $string_id, $link) . ' ';
                $final_translations .= anchor_modal(
                    'admin/lang/string_delete/' . $string_id,
                    t('Supprimer', [], 'admin/lang/bar'),
                    ['class' => 'icon icon_bin']
                );
                $final_translations .= '</div>';
                foreach ($string as $lid => $translation) {
                    $final_translations .= '<div class="t_line">';
                    $final_translations .= '<div class="t_title">' . t(I18N::languages((int) $lid, 'name'), [], 'languages') . '</div>';
                    $final_translations .= strip_tags($translation->text);
                    $final_translations .= '</div>';
                }
                $final_translations .= '</div>';
            }
            $final_strings .= '</div>';
        }

        //TODO :: transform to the new system
        if (isset(CI()->taxonomy)) {
            foreach (CI()->taxonomy->admin_taxonomy as $vid => $strings) {
                $final_contexts .= '<div class="t_line context" title="taxonomies_' . $vid . '">' . t('Vocabulaire', [], 'admin/lang/bar') . ': ' . t(Taxonomy::vocabulary($vid), [], 'vocabulary') . '</div>';
                $final_strings .= '<div id="taxonomies_' . $vid . '" class="t_internal" style="display:none;">';
                foreach ($strings as $term) {
                    if ($term['translated']) {
                        $final_strings .= '<div class="t_line string" title="taxonomy_' . $term['term_id'] . '">' . $term . '</div>';
                    } else {
                        $final_strings .= '<div class="t_line string not" title="taxonomy_' . $term['term_id'] . '">' . $term['lang_fr']['title'] . '</div>';
                    }

                    $final_translations .= '<div id="taxonomy_' . $term['term_id'] . '" class="t_internal" style="display:none;">';
                    $final_translations .= '<div class="t_links t_bar">';
                    $final_translations .= anchor_modal('admin/taxonomy/term_edit/' . $term['term_id'], t('Editer', [], 'admin/lang/bar'), ['class' => 'icon icon_pencil']);
                    $final_translations .= '</div>';
                    if (Taxonomy::isTranslatable($term['vocabulary_id'])) {
                        foreach (I18N::languages() as $lang => $name) {
                            if ($term->translated($lang)) {
                                $final_translations .= '<div class="t_line">';
                                $final_translations .= '<div class="t_title">' . t($name['name'], [], 'languages') . '</div>';
                                $final_translations .= strip_tags($term->title($lang));
                                $final_translations .= '</div>';
                            }
                        }
                    } else {
                        $final_translations .= '<hr class="min" />';
                        $final_translations .= strip_tags($term);
                    }
                    $final_translations .= '</div>';
                }
                $final_strings .= '</div>';
            }
        }

        $lang_links = '<div class="lang_links">';

        foreach (I18N::languages() as $lang => $name) {
            if ($lang == I18N::getCurrent()) {
                $lang_links .= '<div class="t_link">' . ucfirst(t($name['name'], [], 'languages')) . '</div> ';
            } else {
                $lang_links .= anchor('lang/index/' . $lang, ucfirst(t($name['name'], [], 'languages')), ['class' => 't_link']) . ' ';
            }
        }
        $lang_links .= '</div>';

        $output->output .= '
            <div class="faire_valoir_top">&nbsp;</div>
            <div class="faire_valoir_bottom">&nbsp;</div>
            <div id="translation_section">
                <div class="translation_bar t_bar">
                    <div class="t_link" id="t_show" style="display:none;">' . t('Afficher', [], 'admin/lang/bar') . '</div>
                    <div class="t_link" id="t_hide">' . t('Cacher', [], 'admin/lang/bar') . '</div>
                    <div class="t_link" id="t_disable">' . t('Désactiver', [], 'admin/lang/bar') . '</div>
                    <div class="t_link">' . anchor('admin/lang/generate', t('Appliquer', [], 'admin/lang/bar')) . '</div>
                    ' . $lang_links . '
                    <strong>' . t('Barre de langue', [], 'admin/lang/bar') . '</strong>
                </div>
                <div class="translation_titles t_bar">
                    <div class="presentation contexts">' . t('Contextes', [], 'admin/lang/bar') . '</div>
                    <div class="presentation strings">' . t('Chaines', [], 'admin/lang/bar') . '</div>
                    <div class="presentation string">' . t('Informations', [], 'admin/lang/bar') . '</div>
                </div>
                <div class="translation_internal">
                    <div class="presentation contexts">
                        <div class="t_internal">' . $final_contexts . '</div>
                    </div>
                    <div class="presentation strings">
                        ' . $final_strings . '
                    </div>
                    <div class="presentation string">
                        ' . $final_translations . '
                    </div>
                </div>
             </div>';

        JS::ready(
            '$("#t_show, #t_hide").click(function () { $(".translation_internal, .translation_titles, .faire_valoir_bottom").toggle(); $("#t_show, #t_hide").toggle();  });

            $("#t_disable").click(function () {
                jQuery.setCookie("YMW_Lang_Bar", "-", {path:"/"});
                window.location.reload();
            });

            $(".context.t_line").click(function () {
                //mark as selected
                $(".context.t_line").removeClass("selected");
                $(this).addClass("selected");

                //hide current strings
                $(".strings .t_internal").hide();

                //hide current info string
                $(".string .t_internal").hide();

                title = $(this).attr("title");
                $("#" + title).show();
            });

            $(".string.t_line").click(function () {
                //mark as selected
                $(".string.t_line").removeClass("selected");
                $(this).addClass("selected");

                //hide current info string
                $(".string .t_internal").hide();

                title = $(this).attr("title");
                $("#" + title).show();
            });'
        );
    }
);

/*
 * Loads the ability to translate contents on the fly
 */
Event::listen(
    'init',
    function () {
        if (!Request::ajax()) {
            if (Gate::allows('admin_language_string_edit')) {
                JS::ready(
                    '$(".not_translated").css("color", "red").click(function () {
                        jQuery.facebox({ ajax: "' . URL::to('admin/lang/string_edit') . '/" + $(this).attr("title") });
                    });'
                );
            }

            if (Gate::allows('admin_term_edit')) {
                JS::ready(
                    '$(".not_tagged").css("color", "red").click(function () {
                        jQuery.facebox({ ajax: "' . URL::to('admin/taxonomy/term_edit') . '/" + $(this).attr("title") });
                    });'
                );
            }
        }
    }
);
