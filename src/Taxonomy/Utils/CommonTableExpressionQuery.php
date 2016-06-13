<?php

/**
 * This class will use the Common Table Expression
 * technique to make a recursive request on the database.
 * On mysql it will fallback to a stored procedure doing the same.
 *
 * The choice of a Common Table Expression or a standard query is done automatically
 */

namespace Rocket\Taxonomy\Utils;

use Illuminate\Support\Facades\DB;

/**
 * Class using the DB's internal Recursive query mechanism to query term hierarchies.
 */
class CommonTableExpressionQuery extends RecursiveQuery implements RecursiveQueryInterface
{
    /**
     * Execute the query with the current PDO connection.
     * Will log the time taken manually
     *
     * @param string $raw_query
     * @param int $id
     * @return \Illuminate\Support\Collection
     */
    protected function runQuery($raw_query, $id)
    {
        $query = str_replace(':id', $id, $raw_query);

        $start = microtime(true);

        // Does not work as a prepared statement; we have to execute it directly
        $results = DB::getReadPdo()->query($query)->fetchAll(\PDO::FETCH_OBJ);

        // Log the query manually
        DB::logQuery($raw_query, [$id], round((microtime(true) - $start) * 1000, 2));

        return $results;
    }

    /**
     * Prepare the complete query to run on the DB to get the data recursively.
     *
     * @param string $initial The initial query bootstrapping the recursive query
     * @param string $recursive The query to dig deeper in the hierarchy
     * @return string
     */
    protected function assembleQuery($initial, $recursive)
    {
        $tmp_tbl = 'name_tree';
        $recursive = str_replace(':tmp_tbl', $tmp_tbl, $recursive);

        $final = "select distinct * from $tmp_tbl";

        if (DB::connection()->getDriverName() == 'mysql') {
            return "Call WITH_EMULATOR('$tmp_tbl', '$initial', '$recursive', '$final', 0, 'ENGINE=MEMORY');";
        }

        return "WITH RECURSIVE $tmp_tbl AS ($initial UNION ALL $recursive) $final;";
    }

    /**
     * Get all ancestors of a term
     *
     * @param int $id The term ID
     * @return \Illuminate\Support\Collection
     */
    public function getAncestry($id)
    {
        $tbl = $this->hierarchyTable;
        $recursive = "SELECT c.term_id, c.parent_id from $tbl as c join :tmp_tbl as p on p.parent_id = c.term_id";

        $raw_query = $this->assembleQuery($this->getAncestryInitialQuery(), $recursive);

        return $this->runQuery($raw_query, $id);
    }

    /**
     * Get all descendants of a term.
     *
     * @param int $id The term ID
     * @return \Illuminate\Support\Collection
     */
    public function getDescent($id)
    {
        $tbl = $this->hierarchyTable;
        $recursive = "SELECT c.term_id, c.parent_id from $tbl as c join :tmp_tbl as p on c.parent_id = p.term_id";

        $raw_query = $this->assembleQuery($this->getDescentInitialQuery(), $recursive);

        return $this->runQuery($raw_query, $id);
    }
}
