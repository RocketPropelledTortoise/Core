<?php namespace Rocket\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use ErrorException;
use Rocket\UI\Forms\Fields\Date;

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
     * @var
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
    public function revision($language)
    {
        if ($language == $this->language) {
            return clone $this;
        }

        $revision = new static();
        $revision->language = $this->language;
        $revision->content_id = $this->language;

        return $revision;
    }

    /**
     * Get the database friendly content type
     *
     * @return mixed
     */
    public function getContentType()
    {
        return str_replace('\\', '', snake_case(class_basename($this)));
    }

    protected function hasField($field)
    {
        return array_key_exists($field, $this->fields);
    }

    protected function isContentField($field)
    {
        return in_array($field, ['id', 'created_at', 'updated_at']);
    }

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
