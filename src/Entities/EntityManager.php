<?php namespace Rocket\Entities;

use Illuminate\Support\Facades\DB;

/**
 * Represents a content of any form
 *
 * @property int $id the entity's ID
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
     * @param Entity $entity
     */
    public function save(Entity $entity)
    {
        DB::transaction(
            function () use ($entity) {
                // Save content
                $content = $entity->getContent();
                $content->save();

                // Create revision ID
                $revision = $entity->getRevision();
                $revision->content_id = $content->id;
                $revision->save();

                // Prepare and save fields
                foreach (array_keys($entity->getFields()) as $fieldName) {
                    $field = $entity->getField($fieldName);
                    $field->each(function (Field $value, $key) use ($revision, $fieldName) {
                        $value->weight = $key;
                        $value->revision_id = $revision->id;
                        $value->name = $fieldName;

                        $value->save();
                    });
                }
            }
        );
    }

    /**
     * Update a revision
     *
     * @param Entity $entity
     */
    public function updateRevision(Entity $entity)
    {
        //TODO : do we implement update functionnality ?
    }

    /**
     * Delete a revision ? Remove content ?
     *
     * @return bool|null|void
     */
    public function deleteRevision(Entity $entity, $revision_id)
    {
        //TODO
    }
}
