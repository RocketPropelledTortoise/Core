<?php

/**
 * Interface for classes making recursive queries.
 */

namespace Rocket\Taxonomy\Utils;

/**
 * Interface for classes making recursive queries.
 */
interface RecursiveQueryInterface
{
    /**
     * Get all ancestors of a term
     *
     * @param int $id The term ID
     * @return \Illuminate\Support\Collection
     */
    public function getAncestry($id);

    /**
     * Get all descendants of a term
     *
     * @param int $id The term ID
     * @return \Illuminate\Support\Collection
     */
    public function getDescent($id);
}
