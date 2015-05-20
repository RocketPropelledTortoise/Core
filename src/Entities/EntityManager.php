<?php namespace Rocket\Entities;

use Illuminate\Support\Facades\DB;

/**
 * Represents a content of any form
 *
 * @property integer $id the entity's ID
 */
class EntityManager
{
    /**
     * Get the database friendly content type
     *
     * @return string
     */
    public function getContentType($entity)
    {
        return str_replace('\\', '', snake_case(class_basename($entity)));
    }

    /**
     * Save a revision
     *
     * @return bool|void
     */
    public function save(array $options = [])
    {
        //TODO

        DB::transaction(
            function () {
                //save all revisions
            }
        );
    }

    public function update(array $attributes = [])
    {
        //TODO : do we implement update functionnality ?
    }

    /**
     * Delete a revision ? Remove content ?
     *
     * @return bool|null|void
     */
    public function delete()
    {
        //TODO
    }
}
