<?php
/**
 * The Term Repository handles the retrieval of Taxonomy terms.
 */

namespace Rocket\Taxonomy\Repositories;

/**
 * Interface TermRepositoryInterface
 */
interface TermRepositoryInterface
{
    /**
     * Get a term with all translations
     *
     * @param int $term_id
     * @param bool $from_cache
     * @return \Rocket\Taxonomy\Term
     */
    public function getTerm($term_id, $from_cache = true);

    /**
     * Remove a term from the cache
     *
     * @param int $term_id
     * @return bool
     */
    public function uncacheTerm($term_id);
}
