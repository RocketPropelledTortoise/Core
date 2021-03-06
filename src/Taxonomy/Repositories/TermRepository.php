<?php
/**
 * The Term Repository handles the retrieval of Taxonomy terms.
 */
namespace Rocket\Taxonomy\Repositories;

use Illuminate\Cache\Repository as CacheRepository;
use Rocket\Taxonomy\Model\TermContainer;
use Rocket\Taxonomy\Model\TermData;
use Rocket\Taxonomy\Support\Laravel5\Facade as T;
use Rocket\Taxonomy\Term;
use Rocket\Translation\Support\Laravel5\Facade as I18N;

/**
 * Interface TermRepositoryInterface
 */
class TermRepository implements TermRepositoryInterface
{
    /**
     * @var CacheRepository The cache to store the terms in
     */
    protected $cache;

    /**
     * @var string The term cache key prefix
     */
    protected static $cacheKey = 'Rocket::Taxonomy::Term::';

    /**
     * TermRepository constructor.
     *
     * @param CacheRepository $cache The cache in which to keep terms
     */
    public function __construct(CacheRepository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get a term with all translations
     *
     * @param int $term_id
     * @param bool $from_cache
     * @return \Rocket\Taxonomy\Term
     */
    public function getTerm($term_id, $from_cache = true)
    {
        if (!$from_cache || !$data = $this->cache->get(self::$cacheKey . $term_id)) {
            $data = $this->cacheTerm($term_id);
        }

        if (!$data) {
            return;
        }

        return new Term($data);
    }

    /**
     * Remove a term from the cache
     *
     * @param int $term_id
     * @return bool
     */
    public function uncacheTerm($term_id)
    {
        return $this->cache->forget(self::$cacheKey . $term_id);
    }

    /**
     * Puts the term in the cache and returns it for usage
     *
     * @param  int $term_id
     * @return array
     */
    protected function cacheTerm($term_id)
    {
        $term = TermContainer::with('translations')->find($term_id);

        if (!$term || !count($term->translations)) {
            return false;
        }

        $translations = [];
        foreach ($term->translations as $t) {
            $translations[$t->language_id] = $t;
        }

        $first = $term->translations[0];

        $final_term = [
            'term_id' => $term_id,
            'vocabulary_id' => $term->vocabulary_id,
            'type' => $term->type,
        ];

        if (T::isTranslatable($term->vocabulary_id)) {
            foreach (I18N::languages() as $lang => $l) {
                if (array_key_exists($l['id'], $translations)) {
                    $term = $translations[$l['id']];
                } else {
                    $term = new TermData();
                    $term->term_id = $term_id;
                    $term->language_id = $l['id'];
                    $term->title = $first->title;
                    $term->description = $first->description;
                    $term->translated = false;
                }

                $final_term['lang_' . $lang] = $term->toArray();
            }
        } else {
            $final_term['has_translations'] = false;
            $final_term['lang'] = $first;
        }

        $this->cache->put(self::$cacheKey . $term_id, $final_term, 60 * 0);

        return $final_term;
    }
}
