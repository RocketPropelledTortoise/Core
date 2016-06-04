<?php namespace Rocket\Entities;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Rocket\Entities\Exceptions\InvalidFieldTypeException;
use Rocket\Entities\Exceptions\NonExistentFieldException;
use Rocket\Entities\Exceptions\ReservedFieldNameException;

/**
 * Entity manager
 *
 * @property int $id The content ID
 * @property int $language_id The language in which this entity is
 * @property-read \DateTime $created_at
 * @property-read \DateTime $updated_at
 */
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

    /**
     * Entity constructor.
     *
     * @param int $language_id The language this specific entity is in
     */
    public function __construct($language_id)
    {
        if (!is_int($language_id) || $language_id == 0) {
            throw new InvalidArgumentException("You must set a valid 'language_id'.");
        }

        $fields = $this->getFields();

        $this->initialize($fields);

        $this->revision->language_id = $language_id;
    }

    protected function initialize(array $fields)
    {
        $this->content = new Content;
        $this->revision = new Revision;

        foreach ($fields as $field => $settings) {
            $this->data[$field] = $this->initializeField($field, $settings);
        }
    }

    /**
     * @param string $field
     * @param array $settings
     * @return static
     * @throws InvalidFieldTypeException
     * @throws ReservedFieldNameException
     */
    protected function initializeField($field, $settings)
    {
        if ($this->isContentField($field) || $this->isRevisionField($field)) {
            throw new ReservedFieldNameException(
                "The field '$field' cannot be used in '" . get_class($this) . "' as it is a reserved name"
            );
        }

        $type = $settings['type'];

        if (!array_key_exists($type, self::$types)) {
            throw new InvalidFieldTypeException("Unkown type '$type' in '" . get_class($this) . "'");
        }

        $settings['type'] = self::$types[$settings['type']];

        return FieldCollection::initField($settings);
    }

    abstract public function getFields();

    /**
     * Get the database friendly content type
     *
     * @return string
     */
    public static function getContentType()
    {
        return str_replace('\\', '', snake_case((new \ReflectionClass(get_called_class()))->getShortName()));
    }

    /**
     * Create a new revision based on the same content ID but without the content.
     * Very useful if you want to add a new language
     *
     * @param int $language_id
     * @return static
     */
    public function newRevision($language_id = null)
    {
        $created = new static($language_id ?: $this->language_id);
        $created->content = $this->content;

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
     * @param string $field
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
     * @throws NonExistentFieldException
     * @return $this|bool|\Carbon\Carbon|\DateTime|mixed|static
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

        throw new NonExistentFieldException("Field '$key' doesn't exist in '" . get_class($this) . "'");
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param string $key
     * @param mixed $value
     * @throws NonExistentFieldException
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

                return;
            }

            $this->getField($key)->offsetSet(0, $value);

            return;
        }

        throw new NonExistentFieldException("Field '$key' doesn't exist in '" . get_class($this) . "'");
    }

    /**
     * Find the latest valid revision for this entity
     *
     * @param int $id
     * @param int $language_id
     * @return static
     */
    public static function find($id, $language_id)
    {
        $instance = new static($language_id);

        $instance->content = Content::findOrFail($id);

        // TODO :: logic to get valid revision, this will retrieve only one revision
        $instance->revision = Revision::where('content_id', $id)->where('language_id', $language_id)->firstOrFail();

        (new Collection($instance->getFields()))
            ->map(function ($options) {
                    return $options['type'];
            })
            ->values()
            ->unique()
            ->map(function ($type) {
                    return self::$types[$type];
            })
            ->each(
                function ($type) use ($instance) {
                    $type::where('revision_id', $instance->revision->id)
                        ->get()
                        ->each(function (Field $value) use ($instance) {
                                $instance->data[$value->name][$value->weight] = $value;
                        });
                }
            );

        return $instance;
    }

    /**
     * Save a revision
     */
    public function save($newRevision = false)
    {
        if ($newRevision) {
            $revision = new Revision;
            $revision->language_id = $this->revision->language_id;

            $this->revision = $revision;
        }

        DB::transaction(
            function () use ($newRevision) {
                // Save content
                $this->content->save();

                // Create revision ID
                $this->revision->content_id = $this->content->id;
                $this->revision->save();

                // Prepare and save fields
                foreach (array_keys($this->data) as $fieldName) {
                    $field = $this->data[$fieldName];

                    //TODO :: remove deleted fields

                    $field->each(
                        function (Field $value, $key) use ($newRevision, $fieldName) {

                            if ($newRevision) {
                                $value->id = null;
                                $value->created_at = null;
                            }

                            $value->weight = $key;
                            $value->revision_id = $this->revision->id;
                            $value->name = $fieldName;

                            $value->save();
                        }
                    );
                }
            }
        );
    }

    /**
     * Convert the Entity to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $content = [
            '_content' => $this->content->toArray(),
            '_revision' => $this->revision->toArray(),
        ];

        foreach ($this->data as $field => $data) {
            $content[$field] = $data->toArray();
        }

        return $content;
    }
}
