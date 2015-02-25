<?php namespace Rocket\Entities;

use ErrorException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Represents a content of any form
 *
 * @property integer $id the entity's ID
 */
class Entity extends Model
{
    /**
     * {@inheritdoc}
     */
    public $table = 'contents';

    //belongsToMany : revisions

    /**
     * Give the field names for this content type
     *
     * @var array
     */
    protected $fields = [];

    /**
     * These fields will not be loaded eagerly
     * @var array
     */
    protected $lazy = [];

    /**
     * The revision represented by this entity
     *
     * @var Revision
     */
    protected $currentRevision;

    public function __construct($data)
    {
        $this->bootIfNotBooted();

        $this->syncOriginal();

        //TODO :: field validators

        $this->currentRevision = new Revision();
        $this->currentRevision->initShape($this->fields);
        $this->currentRevision->language_id = $data['language_id'];
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

                parent::save();
            }
        );
    }

    public function update(array $attributes = [])
    {
        //TODO
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

    /**
     * Start a new revision
     *
     * @param $language
     * @return Entity
     */
    public function revision($language_id)
    {
        if ($language_id == $this->currentRevision->language_id) {
            return clone $this;
        }

        $revision = new static([]);
        $revision->language_id = $language_id;
        $revision->content_id = $this->id;

        return $revision;
    }

    /**
     * Get the database friendly content type
     *
     * @return string
     */
    public function getContentType()
    {
        return str_replace('\\', '', snake_case(class_basename($this)));
    }

    /**
     * Check if the field exists on the entity
     *
     * @param string $field
     * @return bool
     */
    protected function hasField($field)
    {
        return array_key_exists($field, $this->fields);
    }

    /**
     * Check if the field is related to the content
     *
     * @param string $field
     * @return bool
     */
    protected function isContentField($field)
    {
        return in_array($field, ['id', 'created_at', 'updated_at']);
    }

    /**
     * Check if the field is related to the revision
     *
     * @param string $field
     * @return bool
     */
    protected function isRevisionField($field)
    {
        return in_array($field, ['language_id']);
    }

    /**
     * @return Revision
     */
    protected function getCurrentRevision()
    {
        return $this->currentRevision;
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param string $key
     * @return $this|bool|\Carbon\Carbon|\DateTime|mixed|static
     * @throws ErrorException
     */
    public function __get($key)
    {
        if ($this->isContentField($key)) {
            return $this->getAttribute($key);
        }

        if ($this->isRevisionField($key)) {
            return $this->getCurrentRevision()->getAttribute($key);
        }

        if (!$this->hasField($key)) {
            throw new ErrorException("Field '$key' doesn't exists in '".get_class($this)."'");
        }

        return $this->getCurrentRevision()->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed $value
     * @throws ErrorException
     */
    public function __set($key, $value)
    {
        if ($this->isContentField($key)) {
            $this->setAttribute($key, $value);
            return;
        }

        if ($this->isRevisionField($key)) {
            $this->getCurrentRevision()->setAttribute($key, $value);
            return;
        }

        if (!$this->hasField($key)) {
            throw new ErrorException("Field '$key' doesn't exists in '".get_class($this)."'");
        }

        $this->getCurrentRevision()->setAttribute($key, $value);
    }

    /**
     * Determine if an attribute exists on the model.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        //TODO
        return ((isset($this->attributes[$key]) || isset($this->relations[$key])) ||
            ($this->hasGetMutator($key) && ! is_null($this->getAttributeValue($key))));
    }

    /**
     * Unset an attribute on the model.
     *
     * @param  string  $key
     * @return void
     */
    public function __unset($key)
    {
        //TODO
        unset($this->attributes[$key]);

        unset($this->relations[$key]);
    }
}
