<?php

/**
 * This class takes a directed graph and creates all possible paths starting form a single element.
 */

namespace Rocket\Taxonomy\Utils;

use CentralDesktop\Graph\Edge\DirectedEdge;
use CentralDesktop\Graph\Graph\DirectedGraph;
use CentralDesktop\Graph\Vertex;

/**
 * Transforms a Directed Graph to a list of paths
 */
class PathResolver
{
    /**
     * @var array The resolved paths
     */
    protected $paths;

    /**
     * @var array<integer> The path being prepared.
     */
    protected $current_path;

    /**
     * @var DirectedGraph The graph used to create the paths.
     */
    protected $digraph;

    /**
     * PathResolver constructor.
     *
     * @param DirectedGraph $graph The graph used to create the paths.
     */
    public function __construct(DirectedGraph $graph)
    {
        $this->digraph = $graph;
    }

    /**
     * Resolve all paths that can be resolved from the start point.
     *
     * @param Vertex $start_vertex
     * @return array
     */
    public function resolvePaths(Vertex $start_vertex)
    {
        $this->paths = [];

        /**
         * @var DirectedEdge
         */
        foreach ($start_vertex->incoming_edges as $edge) {
            $this->current_path = [$start_vertex->get_data()];
            $this->getPathsRecursion($edge->get_source(), $edge);
        }

        return $this->paths;
    }

    /**
     * Recurse on all paths from the start point
     *
     * @param Vertex $start The Vertex to get started from
     * @param DirectedEdge $edge
     */
    protected function getPathsRecursion(Vertex $start, DirectedEdge $edge)
    {
        // We don't want to visit the same vertex twice within a single path. (avoid loops)
        if (in_array($start->get_data(), $this->current_path)) {
            $this->paths[] = array_reverse($this->current_path);

            return;
        }

        $this->current_path[] = $start->get_data();

        if ($start->incoming_edges->count() == 0) {
            $this->paths[] = array_reverse($this->current_path);

            return;
        }

        /**
         * @var DirectedEdge
         */
        foreach ($start->incoming_edges as $edge) {
            $this->getPathsRecursion($edge->get_source(), $edge);

            //remove the item that was added by the child
            array_pop($this->current_path);
        }
    }
}
