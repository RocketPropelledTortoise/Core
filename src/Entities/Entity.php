<?php namespace Rocket\Entities;

use RuntimeException;
use InvalidArgumentException;


abstract class Entity
{

    public static $types;

    /**
     * The content represented by this entity
     *
     * @var Content
     */
    protected $content; //id, created_at

    /**
     * The revision represented by this entity
     *
     * @var Revision
     */
    protected $revision; //language_id, updated_at

    /**
     * The fields in this entity
     *
     * @var array<FieldCollection>
     */
    protected $data;

    public function __construct($data = [])
    {
        $fields = $this->getFields();

        //TODO :: populate data

        $this->content = new Content;
        $this->revision = new Revision;

        foreach ($fields as $field => $settings) {

            if ($this->isContentField($field) || $this->isRevisionField($field)) {
                throw new InvalidArgumentException(
                    "The field '$field' cannot be used in '" . get_class($this) . "' as it is a reserved name"
                );
            }

            $type = $settings['type'];

            if (!array_key_exists($type, self::$types)) {
                throw new RuntimeException("Unkown type '$type' in '" . get_class($this) . "'");
                //TODO :: use types for something ...
            }

            $this->data[$field] = FieldCollection::initField($settings);
        }
    }

    abstract protected function getFields();

    /**
     * Create a new revision based on the same content ID but without the content.
     * Very useful if you want to add a new language
     *
     * @param integer $language_id
     * @return static
     */
    public function newRevision($language_id = null)
    {
        $created = new static();
        $created->content = $this->content;

        if ($language_id) {
            $created->language_id = $language_id;
        }

        return $created;
    }

    /**
     * Check if the field is related to the content
     *
     * @param string $field
     * @return bool
     */
    protected function isContentField($field)
    {
        return in_array($field, ['id', 'created_at']);
    }

    /**
     * Check if the field exists on the entity
     *
     * @param string $field
     * @return bool
     */
    public function hasField($field)
    {
        return array_key_exists($field, $this->data);
    }

    /**
     * @param $field
     * @return FieldCollection
     */
    public function getField($field)
    {
        return $this->data[$field];
    }

    /**
     * Check if the field is related to the revision
     *
     * @param string $field
     * @return bool
     */
    protected function isRevisionField($field)
    {
        return in_array($field, ['language_id', 'updated_at']);
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return $this|bool|\Carbon\Carbon|\DateTime|mixed|static
     * @throws RuntimeException
     */
    public function __get($key)
    {
        if ($this->isContentField($key)) {
            return $this->content->getAttribute($key);
        }

        if ($this->isRevisionField($key)) {
            return $this->revision->getAttribute($key);
        }

        if ($this->hasField($key)) {
            return $this->getField($key);
        }

        throw new RuntimeException("Field '$key' doesn't exist in '".get_class($this)."'");
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed $value
     * @throws RuntimeException
     */
    public function __set($key, $value)
    {
        if ($this->isContentField($key)) {
            $this->content->setAttribute($key, $value);
            return;
        }

        if ($this->isRevisionField($key)) {
            $this->revision->setAttribute($key, $value);
            return;
        }

        if ($this->hasField($key)) {
            if ($value == []) {
                $this->getField($key)->clear();
            } else {
                $this->getField($key)->offsetSet(0, $value);
            }
            return;
        }

        throw new RuntimeException("Field '$key' doesn't exist in '".get_class($this)."'");
    }

    /**
     * Convert the Entity to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $content = [];

        $content['_content'] = $this->content->toArray();
        $content['_revision'] = $this->revision->toArray();

        foreach ($this->data as $field => $data) {
            $content[$field] = $data->toArray();
        }

        return $content;
    }

}
