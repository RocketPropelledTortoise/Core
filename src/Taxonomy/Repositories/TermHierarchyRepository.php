<?php
/**
 * The repository of all hierarchies of terms
 */
namespace Rocket\Taxonomy\Repositories;

use CentralDesktop\Graph\Graph\DirectedGraph;
use CentralDesktop\Graph\Vertex;
use Illuminate\Support\Facades\DB;
use Rocket\Taxonomy\Model\Hierarchy;
use Rocket\Taxonomy\Utils\CommonTableExpressionQuery;
use Rocket\Taxonomy\Utils\PathResolver;
use Rocket\Taxonomy\Utils\RecursiveQuery;

/**
 * Create paths from a term all the way to all the parents.
 *
 * Everything is calculated upside down so that the DFS search for all paths is easy
 */
class TermHierarchyRepository implements TermHierarchyRepositoryInterface
{
    /**
     * @var array<Vertex> all Vertices (Current and parents)
     */
    protected $vertices;

    /**
     * @var \Illuminate\Cache\Repository The cache to store the terms in.
     */
    protected $cache;

    /**
     * Create a Repository
     *
     * @param \Illuminate\Cache\Repository $cache
     */
    public function __construct(\Illuminate\Cache\Repository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Add a parent to this term.
     *
     * @param int $term_id The term
     * @param int $parent_id The parent term
     * @return bool
     */
    public function addParent($term_id, $parent_id)
    {
        return Hierarchy::insert(['term_id' => $term_id, 'parent_id' => $parent_id]);
    }

    /**
     * Remove this term's parents
     *
     * @param int $term_id The term
     * @return bool
     */
    public function unsetParents($term_id)
    {
        return Hierarchy::where('term_id', $term_id)->delete();
    }

    /**
     * Tests if the driver supports Common Table Expression.
     *
     * If it doesn't we'll fall back to a manually recursive query.
     *
     * @return bool
     */
    protected function supportsCommonTableExpressionQuery()
    {
        $driver = DB::connection()->getDriverName();

        if ($driver == 'mysql') {
            return true;
        }

        if ($driver == 'sqlite' && \SQLite3::version()['versionNumber'] >= 3008003) {
            return true;
        }

        return false;
    }

    /**
     * Get the retriever to get the Term's Hierarchy
     *
     * @return \Rocket\Taxonomy\Utils\RecursiveQueryInterface
     */
    protected function getRecursiveRetriever()
    {
        if ($this->supportsCommonTableExpressionQuery()) {
            return new CommonTableExpressionQuery();
        }

        return new RecursiveQuery();
    }

    /**
     * Get the hierarchy cache key
     *
     * @param string $direction The hierarchy's direction
     * @param int $id The term's id
     * @return string
     */
    protected function getCacheKey($direction, $id)
    {
        return "Rocket::Taxonomy::TermHierarchy::$direction::$id";
    }

    /**
     * Get all parents recursively
     *
     * @param int $id The term's id
     * @return \Illuminate\Support\Collection
     */
    public function getAncestry($id)
    {
        $key = $this->getCacheKey('ancestry', $id);
        if ($results = $this->cache->get($key)) {
            return $results;
        }

        $this->cache->add($key, $results = $this->getRecursiveRetriever()->getAncestry($id), 2);

        return $results;
    }

    /**
     * Get all childs recursively.
     *
     * @param int $id The term's id
     * @return \Illuminate\Support\Collection
     */
    public function getDescent($id)
    {
        $key = $this->getCacheKey('descent', $id);
        if ($results = $this->cache->get($key)) {
            return $results;
        }

        $this->cache->add($key, $results = $this->getRecursiveRetriever()->getDescent($id), 2);

        return $results;
    }

    /**
     * Prepare the vertices to create the graph.
     *
     * @param \Illuminate\Support\Collection $data The list of vertices that were retrieved
     * @return array<Vertex>
     */
    protected function prepareVertices($data)
    {
        $vertices = [];
        foreach ($data as $content) {
            // identifiers must be strings or SplObjectStorage::contains fails
            // seems to impact only PHP 5.6
            $content->term_id = "$content->term_id";
            $content->parent_id = "$content->parent_id";

            if (!array_key_exists($content->term_id, $vertices)) {
                $vertices[$content->term_id] = new Vertex($content->term_id);
            }

            if (!array_key_exists($content->parent_id, $vertices)) {
                $vertices[$content->parent_id] = new Vertex($content->parent_id);
            }
        }

        return $vertices;
    }

    /**
     * Get all parents recursively
     *
     * @param int $id The term we want the ancestry from.
     * @return array Vertex, DirectedGraph
     */
    public function getAncestryGraph($id)
    {
        $data = $this->getAncestry($id);

        if (count($data) == 0) {
            return [null, null];
        }

        // Create Vertices
        $this->vertices = $this->prepareVertices($data);

        // Create Graph
        $graph = new DirectedGraph();
        foreach ($this->vertices as $vertex) {
            $graph->add_vertex($vertex);
        }

        // Create Relations
        foreach ($data as $content) {
            $graph->create_edge($this->vertices[$content->parent_id], $this->vertices[$content->term_id]);
        }

        return [$this->vertices[$id], $graph];
    }

    /**
     * Get all childs recursively
     *
     * @param int $id The term we want the descent from.
     * @return array Vertex, DirectedGraph
     */
    public function getDescentGraph($id)
    {
        $data = $this->getDescent($id);

        if (count($data) == 0) {
            return [null, null];
        }

        // Create Vertices
        $this->vertices = $this->prepareVertices($data);

        // Create Graph
        $graph = new DirectedGraph();
        foreach ($this->vertices as $vertex) {
            $graph->add_vertex($vertex);
        }

        // Create Relations
        foreach ($data as $content) {
            $graph->create_edge($this->vertices[$content->term_id], $this->vertices[$content->parent_id]);
        }

        return [$this->vertices[$id], $graph];
    }

    /**
     * Get all the possible paths from this term
     *
     * @param int $id The term we want the ancestry from.
     * @return array<array<int>>
     */
    public function getAncestryPaths($id)
    {
        list($start_vertex, $graph) = $this->getAncestryGraph($id);

        if (!$graph) {
            return [];
        }

        $resolver = new PathResolver($graph);

        return $resolver->resolvePaths($start_vertex);
    }

    /**
     * Get all the possible paths from this term
     *
     * @param int $id The term we want the descent from.
     * @return array<array<int>>
     */
    public function getDescentPaths($id)
    {
        list($start_vertex, $graph) = $this->getDescentGraph($id);

        if (!$graph) {
            return [];
        }

        $resolver = new PathResolver($graph);

        return $resolver->resolvePaths($start_vertex);
    }
}
