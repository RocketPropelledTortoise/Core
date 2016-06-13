<?php

/**
 * Repository of everything that can be done with a Term hierarchy
 */
namespace Rocket\Taxonomy\Repositories;

/**
 * Create paths from a term all the way to all the parents.
 *
 * Everything is calculated upside down so that the DFS search for all paths is easy
 */
interface TermHierarchyRepositoryInterface
{
    /**
     * Get all ancestors
     *
     * @param int $id The term ID
     * @return \Illuminate\Support\Collection
     */
    public function getAncestry($id);

    /**
     * Get all ancestors in a graph
     *
     * @param int $id The term ID
     * @return array Vertex, DirectedGraph
     */
    public function getAncestryGraph($id);

    /**
     * Get all the possible paths from this term
     *
     * @param int $id The term we want the ancestry from.
     * @return array<array<int>>
     */
    public function getAncestryPaths($id);

    /**
     * Get all descendants
     *
     * @param int $id The term ID
     * @return \Illuminate\Support\Collection
     */
    public function getDescent($id);

    /**
     * Get all descendants in a graph
     *
     * @param int $id The term ID
     * @return array Vertex, DirectedGraph
     */
    public function getDescentGraph($id);

    /**
     * Get all the possible paths from this term
     *
     * @param int $id The term we want the descent from.
     * @return array
     */
    public function getDescentPaths($id);

    /**
     * Add a parent to this term.
     *
     * @param int $term_id The term
     * @param int $parent_id The parent term
     * @return bool
     */
    public function addParent($term_id, $parent_id);

    /**
     * Remove this term's parents
     *
     * @param int $term_id
     * @return bool
     */
    public function unsetParents($term_id);
}
